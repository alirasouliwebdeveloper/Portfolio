<?php

namespace App\Services;

use App\DTOs\SearchPerformance;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Reads a page's Google Search performance for the Filament SEO panel.
 * Talks to Google directly (service-account JWT -> OAuth token -> Search
 * Analytics) so no Google SDK is needed. Every failure degrades to "no data"
 * — this must never break the editor.
 */
class SearchConsoleService
{
    private const SCOPE = 'https://www.googleapis.com/auth/webmasters.readonly';

    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const API_URL = 'https://www.googleapis.com/webmasters/v3/sites/%s/searchAnalytics/query';

    private const PERIOD_DAYS = 28;

    /** Search Console data trails real time by about two days. */
    private const LAG_DAYS = 2;

    private const TTL_OK = 21600; // 6 hours — the data itself only moves daily

    private const TTL_FAILED = 600;

    /** @var array<string, mixed>|null */
    private ?array $settings = null;

    /** @var array{client_email: string, private_key: string}|null|false */
    private array|null|false $credentials = false;

    public function isEnabled(): bool
    {
        return (bool) ($this->settings()['enabled'] ?? false)
            && filled($this->siteUrl())
            && $this->credentials() !== null;
    }

    /** The public site origin the queried page URLs are built from. */
    public function pageBaseUrl(): string
    {
        return rtrim((string) ($this->settings()['page_base_url'] ?? '') ?: (string) config('portfolio.frontend_url'), '/');
    }

    /** The service account's address — the user to add in Search Console. */
    public function clientEmail(): ?string
    {
        return $this->credentials()['client_email'] ?? null;
    }

    /**
     * Checks the saved settings against Google and explains, in Persian, what
     * to fix — there is no server shell to debug this from.
     *
     * @return array{ok: bool, message: string}
     */
    public function testConnection(): array
    {
        if (! $this->isEnabled()) {
            return ['ok' => false, 'message' => $this->t('not_configured')];
        }

        try {
            Cache::forget('gsc:token');
            $token = $this->accessToken();

            if ($token === null) {
                return ['ok' => false, 'message' => $this->t('token_failed')];
            }

            $end = Carbon::now()->subDays(self::LAG_DAYS);

            $response = Http::withToken($token)->acceptJson()->timeout(8)->post($this->apiUrl(), [
                'startDate' => $end->copy()->subDays(6)->toDateString(),
                'endDate' => $end->toDateString(),
                'rowLimit' => 1,
            ]);
        } catch (Throwable $exception) {
            return ['ok' => false, 'message' => $this->t('unreachable', ['error' => $exception->getMessage()])];
        }

        if ($response->successful()) {
            return ['ok' => true, 'message' => $this->t('ok', ['site' => $this->siteUrl()])];
        }

        $body = (string) $response->body();

        return ['ok' => false, 'message' => match (true) {
            str_contains($body, 'accessNotConfigured') || str_contains($body, 'SERVICE_DISABLED') => $this->t('api_disabled'),
            in_array($response->status(), [403, 404], true) => $this->t('forbidden', ['site' => $this->siteUrl(), 'email' => $this->clientEmail()]),
            default => $this->t('unexpected', ['status' => $response->status()]),
        }];
    }

    /** Null means "no data" (disabled, misconfigured, or Google said no). */
    public function performance(string $pageUrl, ?string $focusKeyword = null): ?SearchPerformance
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $key = 'gsc:perf:'.md5($pageUrl.'|'.mb_strtolower((string) $focusKeyword).'|'.($this->settings()['_version'] ?? ''));
        $cached = Cache::get($key);

        if ($cached === null) {
            $cached = $this->fetch($pageUrl, $focusKeyword);

            // Keep a failure only briefly so a Google hiccup neither hammers
            // the API on every keystroke nor sticks around for hours.
            Cache::put($key, $cached, $cached['ok'] ? self::TTL_OK : self::TTL_FAILED);
        }

        return $cached['ok'] ? SearchPerformance::fromArray($cached['data']) : null;
    }

    /**
     * The whole property over the last 28 days for the dashboard: totals, a
     * daily series and the top queries and pages. Null when off or on error.
     *
     * @return array{days: int, clicks: int, impressions: int, ctr: float, position: float|null, daily: list<array{date: string, clicks: int, impressions: int}>, queries: list<array{label: string, clicks: int, impressions: int, position: float}>, pages: list<array{label: string, clicks: int, impressions: int, position: float}>}|null
     */
    public function siteSummary(): ?array
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $key = 'gsc:site:'.md5((string) ($this->settings()['_version'] ?? '').$this->siteUrl());
        $cached = Cache::get($key);

        if ($cached === null) {
            $cached = $this->fetchSiteSummary();
            Cache::put($key, $cached, $cached['ok'] ? self::TTL_OK : self::TTL_FAILED);
        }

        return $cached['ok'] ? $cached['data'] : null;
    }

    /** @return array{ok: bool, data?: array<string, mixed>} */
    private function fetchSiteSummary(): array
    {
        try {
            $token = $this->accessToken();

            if ($token === null) {
                return ['ok' => false];
            }

            $end = Carbon::now()->subDays(self::LAG_DAYS);
            $window = [
                'startDate' => $end->copy()->subDays(self::PERIOD_DAYS - 1)->toDateString(),
                'endDate' => $end->toDateString(),
            ];

            $query = fn (string $dimension, int $limit) => Http::withToken($token)->acceptJson()->timeout(8)
                ->post($this->apiUrl(), [...$window, 'dimensions' => [$dimension], 'rowLimit' => $limit]);

            $daily = $query('date', 40);
            $queries = $query('query', 8);
            $pages = $query('page', 8);

            foreach ([$daily, $queries, $pages] as $response) {
                if (! $response->successful()) {
                    Log::warning('Search Console summary failed', ['status' => $response->status()]);

                    return ['ok' => false];
                }
            }

            $days = collect($daily->json('rows') ?? [])->sortBy(fn (array $row) => $row['keys'][0])->values();
            $clicks = (int) $days->sum('clicks');
            $impressions = (int) $days->sum('impressions');
            $weighted = $days->sum(fn (array $row) => $row['position'] * $row['impressions']);

            $rows = fn ($response) => collect($response->json('rows') ?? [])->map(fn (array $row) => [
                'label' => (string) $row['keys'][0],
                'clicks' => (int) $row['clicks'],
                'impressions' => (int) $row['impressions'],
                'position' => round((float) $row['position'], 1),
            ])->values()->all();

            return ['ok' => true, 'data' => [
                'days' => self::PERIOD_DAYS,
                'clicks' => $clicks,
                'impressions' => $impressions,
                'ctr' => $impressions > 0 ? $clicks / $impressions : 0.0,
                'position' => $impressions > 0 ? round($weighted / $impressions, 1) : null,
                'daily' => $days->map(fn (array $row) => [
                    'date' => (string) $row['keys'][0],
                    'clicks' => (int) $row['clicks'],
                    'impressions' => (int) $row['impressions'],
                ])->all(),
                'queries' => $rows($queries),
                'pages' => $rows($pages),
            ]];
        } catch (Throwable $exception) {
            report($exception);

            return ['ok' => false];
        }
    }

    /** @return array{ok: bool, data?: array<string, mixed>} */
    private function fetch(string $pageUrl, ?string $focusKeyword): array
    {
        try {
            $token = $this->accessToken();

            if ($token === null) {
                return ['ok' => false];
            }

            $end = Carbon::now()->subDays(self::LAG_DAYS);
            $start = $end->copy()->subDays(self::PERIOD_DAYS - 1);

            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(8)
                ->post($this->apiUrl(), [
                    'startDate' => $start->toDateString(),
                    'endDate' => $end->toDateString(),
                    'dimensions' => ['query'],
                    'dimensionFilterGroups' => [[
                        'filters' => [['dimension' => 'page', 'operator' => 'equals', 'expression' => $pageUrl]],
                    ]],
                    'rowLimit' => 250,
                ]);

            if (! $response->successful()) {
                Log::warning('Search Console query failed', ['status' => $response->status(), 'body' => $response->body()]);

                return ['ok' => false];
            }

            return [
                'ok' => true,
                'data' => SearchPerformance::fromRows($response->json('rows') ?? [], $focusKeyword, self::PERIOD_DAYS)->toArray(),
            ];
        } catch (Throwable $exception) {
            report($exception);

            return ['ok' => false];
        }
    }

    private function accessToken(): ?string
    {
        return Cache::remember('gsc:token', 3300, function (): ?string {
            $credentials = $this->credentials();

            if ($credentials === null) {
                return null;
            }

            $response = Http::asForm()->timeout(8)->post(self::TOKEN_URL, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $this->signedJwt($credentials),
            ]);

            if (! $response->successful()) {
                Log::warning('Search Console token request failed', ['status' => $response->status()]);

                return null;
            }

            return $response->json('access_token');
        });
    }

    /** @param  array{client_email: string, private_key: string}  $credentials */
    private function signedJwt(array $credentials): string
    {
        $now = time();

        $unsigned = $this->base64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])).'.'.$this->base64Url(json_encode([
            'iss' => $credentials['client_email'],
            'scope' => self::SCOPE,
            'aud' => self::TOKEN_URL,
            'iat' => $now,
            'exp' => $now + 3600,
        ]));

        openssl_sign($unsigned, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256);

        return $unsigned.'.'.$this->base64Url($signature);
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    /** @return array{client_email: string, private_key: string}|null */
    private function credentials(): ?array
    {
        if ($this->credentials !== false) {
            return $this->credentials;
        }

        $decoded = json_decode((string) ($this->settings()['credentials'] ?? ''), true);

        return $this->credentials = (is_array($decoded) && filled($decoded['client_email'] ?? null) && filled($decoded['private_key'] ?? null))
            ? ['client_email' => $decoded['client_email'], 'private_key' => $decoded['private_key']]
            : null;
    }

    /**
     * Settings come from config/services.php (env), so the feature stays off
     * until a Google service account is configured.
     *
     * @return array<string, mixed>
     */
    private function settings(): array
    {
        if ($this->settings !== null) {
            return $this->settings;
        }

        $config = (array) config('services.search_console');
        $credentials = $config['credentials'] ?? null;

        if (blank($credentials) && filled($config['credentials_path'] ?? null) && is_file($config['credentials_path'])) {
            $credentials = file_get_contents($config['credentials_path']);
        }

        return $this->settings = [
            'enabled' => (bool) ($config['enabled'] ?? false),
            'site_url' => $config['site_url'] ?? '',
            'page_base_url' => $config['page_base_url'] ?? '',
            'credentials' => $credentials,
            '_version' => md5((string) $credentials.($config['site_url'] ?? '')),
        ];
    }

    private function siteUrl(): string
    {
        return trim((string) ($this->settings()['site_url'] ?? ''));
    }

    private function apiUrl(): string
    {
        return sprintf(self::API_URL, rawurlencode($this->siteUrl()));
    }

    /** @param  array<string, mixed>  $replace */
    private function t(string $key, array $replace = []): string
    {
        return trans("seo.integration.{$key}", $replace, 'fa');
    }
}
