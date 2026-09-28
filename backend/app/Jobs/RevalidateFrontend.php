<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Tells Next.js which cache tags changed (`POST {NEXT_URL}/api/revalidate`). Tags from one
 * admin save are collected for a few seconds and sent as a single request.
 */
class RevalidateFrontend implements ShouldQueue
{
    use Queueable;

    private const PENDING = 'revalidate:pending';

    private const SCHEDULED = 'revalidate:scheduled';

    public int $tries = 3;

    /** @return list<int> */
    public function backoff(): array
    {
        return [5, 30, 120];
    }

    /** @param  list<string>  $tags */
    public static function schedule(array $tags): void
    {
        if ($tags === [] || ! config('portfolio.revalidate_secret')) {
            return;
        }

        Cache::put(self::PENDING, array_values(array_unique([...Cache::get(self::PENDING, []), ...$tags])), 600);

        // The first change in a window schedules the job; later ones just add their tags.
        if (Cache::add(self::SCHEDULED, true, 5)) {
            dispatch(new self)->delay(now()->addSeconds(3))->afterCommit();
        }
    }

    public function handle(): void
    {
        Cache::forget(self::SCHEDULED);
        $tags = Cache::pull(self::PENDING, []);

        if ($tags === []) {
            return;
        }

        try {
            Http::withToken((string) config('portfolio.revalidate_secret'))
                ->acceptJson()
                ->timeout(10)
                ->post(rtrim((string) config('portfolio.next_url'), '/').'/api/revalidate', ['tags' => $tags])
                ->throw();
        } catch (Throwable $e) {
            // Put the tags back so the retry sends them again.
            Cache::put(self::PENDING, array_values(array_unique([...Cache::get(self::PENDING, []), ...$tags])), 600);

            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Frontend revalidation failed after retries: '.$exception->getMessage(), [
            'tags' => Cache::get(self::PENDING, []),
        ]);
    }
}
