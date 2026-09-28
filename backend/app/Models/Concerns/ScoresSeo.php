<?php

namespace App\Models\Concerns;

use App\Support\Seo\SeoAnalyzer;
use App\Support\Seo\SeoInput;

/** Caches the on-page SEO score (0–100) in `seo_score` every time the record is saved. */
trait ScoresSeo
{
    public static function bootScoresSeo(): void
    {
        static::saving(function ($model): void {
            $model->seo_score = app(SeoAnalyzer::class)->analyze($model->seoInput())->score;
        });
    }

    abstract public function seoInput(): SeoInput;
}
