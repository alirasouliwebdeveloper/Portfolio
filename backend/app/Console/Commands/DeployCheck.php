<?php

namespace App\Console\Commands;

use App\Services\DeployRunner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/** Run every minute from cron on cPanel: deploys the rolling GitHub release when it is new. */
class DeployCheck extends Command
{
    protected $signature = 'deploy:check';

    protected $description = 'Deploy the rolling GitHub release if it is newer than what is already deployed';

    public function handle(DeployRunner $runner): int
    {
        $latest = $runner->latestRelease();

        if (! $latest) {
            $this->error('Release lookup failed (network, rate limit or no release yet) — skipping this tick.');

            return self::FAILURE;
        }

        $marker = storage_path('app/last_deployed_release_id.txt');
        $lastId = is_file($marker) ? trim((string) file_get_contents($marker)) : null;

        if ((string) $latest['id'] === $lastId) {
            return self::SUCCESS;
        }

        $this->info("New release {$latest['id']} ({$latest['published_at']}) — deploying...");

        $results = $runner->run();
        $ok = $results['backend']['ok'] && $results['frontend']['ok'];

        Log::info('deploy:check ran', ['ok' => $ok, 'release_id' => $latest['id'], 'results' => $results]);

        if (! $ok) {
            // Marker left alone, so the next tick retries on its own.
            $this->error('Deploy failed — will retry next tick. See storage/logs/laravel.log.');

            return self::FAILURE;
        }

        file_put_contents($marker, (string) $latest['id']);
        $this->info('Deploy succeeded.');

        return self::SUCCESS;
    }
}
