<?php

namespace App\Console\Commands;

use App\Enums\PublishStatus;
use App\Jobs\RevalidateFrontend;
use App\Models\Category;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Support\ContentCache;
use App\Support\ContentTags;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * A future `published_at` never fires a model event when its time arrives, so this refreshes the
 * API cache and the Next.js pages of everything that went live since the previous run.
 */
class PublishScheduled extends Command
{
    protected $signature = 'content:publish-scheduled';

    protected $description = 'Refresh caches for content whose scheduled publish time has passed';

    private const LAST_RUN = 'content:publish-scheduled:last-run';

    public function handle(): int
    {
        $now = now();
        // First run (or a lost cache) looks back a day so nothing scheduled is missed.
        $since = Carbon::parse(Cache::get(self::LAST_RUN, $now->copy()->subDay()->toIso8601String()));

        $tags = [];
        foreach ([Post::class, Project::class, Service::class, Category::class] as $model) {
            $model::query()
                ->where('status', PublishStatus::Published->value)
                ->where('published_at', '>', $since)
                ->where('published_at', '<=', $now)
                ->each(function ($record) use (&$tags) {
                    $tags = [...$tags, ...ContentTags::for($record)];
                });
        }

        Cache::forever(self::LAST_RUN, $now->toIso8601String());

        $tags = array_values(array_unique($tags));
        if ($tags !== []) {
            ContentCache::flush($tags);
            RevalidateFrontend::schedule($tags);
        }

        $this->info('Refreshed '.count($tags).' tags.');

        return self::SUCCESS;
    }
}
