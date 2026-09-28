<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Support\Seo\SeoAnalyzer;
use Illuminate\Console\Command;

class RescoreSeo extends Command
{
    protected $signature = 'seo:rescore';

    protected $description = 'Recalculate the cached SEO score of every page-like record';

    public function handle(): int
    {
        $total = 0;

        foreach ([Post::class, Project::class, Service::class, Category::class, Page::class] as $model) {
            $model::query()->each(function ($record) use (&$total) {
                $record->seo_score = app(SeoAnalyzer::class)->analyze($record->seoInput())->score;
                $record->saveQuietly();
                $total++;
            });
        }

        $this->info("Rescored {$total} records.");

        return self::SUCCESS;
    }
}
