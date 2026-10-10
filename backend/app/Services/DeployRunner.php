<?php

namespace App\Services;

use App\Jobs\RevalidateFrontend;
use FilesystemIterator;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;
use ZipArchive;

/**
 * cPanel deploys (see DEPLOY_CPANEL.md): GitHub Actions publishes backend.zip/frontend.zip to a
 * rolling GitHub Release and this server pulls them itself — shared hosting firewalls tend to
 * block inbound FTP/webhooks from GitHub's cloud IPs, while outbound calls to api.github.com work.
 * Used by the `deploy:check` cron command (the automated path) and the manual /deploy-hook.
 */
class DeployRunner
{
    /** Every base cache tag the frontend uses (src/lib/api.ts), plus "all" for a full path purge. */
    private const ALL_TAGS = [
        'all', 'about', 'categories', 'faqs', 'pages', 'posts', 'projects',
        'redirects', 'search', 'services', 'settings', 'sitemap', 'testimonials',
    ];

    /** Release metadata only (no download) — cheap enough to check every minute. */
    public function latestRelease(): ?array
    {
        try {
            $response = $this->github()->timeout(30)->get($this->releaseUrl());
        } catch (Throwable $e) {
            // Usually the host blocking outbound HTTPS or a missing CA bundle: say so in the log.
            Log::warning('deploy: GitHub release lookup failed: '.$e->getMessage());

            return null;
        }

        if (! $response->ok()) {
            return null;
        }

        return ['id' => $response->json('id'), 'published_at' => $response->json('published_at')];
    }

    /**
     * Downloads and extracts both assets, runs migrations and cache rebuilds, restarts the
     * Node.js app and queues a full frontend revalidation. Never throws: always returns a
     * results array so the caller (cron or HTTP) can report a partial failure cleanly.
     */
    public function run(): array
    {
        // Some shared hosts disable set_time_limit entirely.
        if (function_exists('set_time_limit')) {
            @set_time_limit(300);
        }

        try {
            $release = $this->github()->timeout(30)->get($this->releaseUrl());
        } catch (Throwable $e) {
            $error = ['ok' => false, 'error' => 'release lookup failed: '.$e->getMessage()];

            return ['backend' => $error, 'frontend' => $error];
        }
        if (! $release->ok()) {
            $error = ['ok' => false, 'error' => "release lookup failed: HTTP {$release->status()}"];

            return ['backend' => $error, 'frontend' => $error];
        }
        $assets = collect($release->json('assets'));

        $backend = $this->deployAsset($assets->firstWhere('name', 'backend.zip'), base_path());
        if ($backend['ok']) {
            $this->fixPermissions();
        }

        $frontendPath = (string) config('services.deploy.frontend_path');
        if ($frontendPath === '') {
            $frontend = ['ok' => false, 'error' => 'DEPLOY_FRONTEND_PATH is not configured'];
        } else {
            $frontend = $this->deployAsset($assets->firstWhere('name', 'frontend.zip'), $frontendPath);
            if ($frontend['ok']) {
                $this->removeStrayFrontendFiles($frontendPath);
                // Passenger restarts the Node.js app when this file's mtime changes.
                @mkdir("{$frontendPath}/tmp", 0755, true);
                @touch("{$frontendPath}/tmp/restart.txt");
            }
        }

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        $results = ['backend' => $backend, 'frontend' => $frontend];

        if ($backend['ok']) {
            foreach ([
                'migrate' => ['--force' => true],
                'optimize' => [],
                'queue:restart' => [],
            ] as $command => $params) {
                try {
                    Artisan::call($command, $params);
                    $results[$command] = ['ok' => true, 'output' => trim(Artisan::output())];
                } catch (Throwable $e) {
                    $results[$command] = ['ok' => false, 'error' => $e->getMessage()];
                }
            }

            if (! file_exists(public_path('storage'))) {
                Artisan::call('storage:link');
            }
        }

        if ($frontend['ok']) {
            // The new build was pre-rendered in CI; this refreshes every page from live content.
            try {
                RevalidateFrontend::schedule(self::ALL_TAGS);
            } catch (Throwable $e) {
                Log::warning('Post-deploy revalidation could not be queued: '.$e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Streams one release asset to disk and extracts it over `$destination`. Private-repo assets
     * need the API `url` with an octet-stream Accept header; public ones work the same way.
     */
    private function deployAsset(?array $asset, string $destination): array
    {
        if (! $asset) {
            return ['ok' => false, 'error' => 'asset not found in release '.config('services.deploy.release_tag')];
        }

        $zipPath = storage_path('app/_deploy_'.$asset['name']);

        try {
            $download = $this->github()
                // replaceHeaders, not withHeaders: the latter merges with the JSON Accept header
                // and GitHub then answers with the asset's metadata instead of the file.
                ->replaceHeaders(['Accept' => 'application/octet-stream'])
                ->withOptions(['sink' => $zipPath])
                ->timeout(180)
                ->get($asset['url']);
        } catch (Throwable $e) {
            @unlink($zipPath);

            return ['ok' => false, 'error' => 'asset download failed: '.$e->getMessage()];
        }

        if (! $download->ok()) {
            @unlink($zipPath);

            return ['ok' => false, 'error' => "asset download failed: HTTP {$download->status()}"];
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            @unlink($zipPath);

            return ['ok' => false, 'error' => 'zip open failed'];
        }

        // .env is never in the zip (CI excludes it), so extracting in place can't touch it.
        $ok = $zip->extractTo($destination);
        $zip->close();
        @unlink($zipPath);

        return $ok ? ['ok' => true] : ['ok' => false, 'error' => "extract to {$destination} failed"];
    }

    private function github(): PendingRequest
    {
        $token = (string) config('services.deploy.github_token');
        $request = Http::acceptJson()->withUserAgent(config('app.name').' deployer');

        // Optional for a public repo, but it lifts the 60 requests/hour anonymous limit that a
        // shared-hosting IP (and a once-a-minute poll) would otherwise exhaust.
        return $token === '' ? $request : $request->withToken($token);
    }

    private function releaseUrl(): string
    {
        return sprintf(
            'https://api.github.com/repos/%s/releases/tags/%s',
            config('services.deploy.github_repo'),
            config('services.deploy.release_tag'),
        );
    }

    /**
     * Source files a 2026-10-10 build traced into the standalone package by mistake. Extracting
     * never deletes, so they are removed here; a standalone build never contains these paths.
     */
    private const STRAY_FRONTEND_PATHS = [
        'src', 'tests', 'scripts', 'AGENTS.md', 'CLAUDE.md', 'README.md', 'eslint.config.mjs',
        'next.config.ts', 'package-lock.json', 'playwright.config.ts', 'postcss.config.mjs',
        'tsconfig.json', 'vitest.config.mts',
    ];

    private function removeStrayFrontendFiles(string $frontendPath): void
    {
        foreach (self::STRAY_FRONTEND_PATHS as $relative) {
            $path = $frontendPath.'/'.$relative;

            if (is_file($path) || is_link($path)) {
                @unlink($path);
            } elseif (is_dir($path)) {
                $items = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach ($items as $item) {
                    $item->isDir() && ! $item->isLink() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
                }
                @rmdir($path);
            }
        }
    }

    private function fixPermissions(): void
    {
        foreach ([storage_path(), base_path('bootstrap/cache')] as $dir) {
            if (! is_dir($dir)) {
                continue;
            }

            @chmod($dir, 0775);

            $items = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($items as $item) {
                @chmod($item->getPathname(), $item->isDir() ? 0775 : 0664);
            }
        }
    }
}
