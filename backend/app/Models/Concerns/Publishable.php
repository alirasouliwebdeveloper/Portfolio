<?php

namespace App\Models\Concerns;

use App\Enums\PublishStatus;
use Illuminate\Database\Eloquent\Builder;

/**
 * Published scope = status is "published" AND published_at <= now().
 * Casts must be merged by the model (see initializePublishable).
 */
trait Publishable
{
    public static function bootPublishable(): void
    {
        static::saving(function ($model): void {
            if ($model->status === PublishStatus::Published && $model->published_at === null) {
                $model->published_at = now();
            }
        });
    }

    public function initializePublishable(): void
    {
        $this->mergeCasts([
            'status' => PublishStatus::class,
            'published_at' => 'datetime',
        ]);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where($query->qualifyColumn('status'), PublishStatus::Published->value)
            ->where($query->qualifyColumn('published_at'), '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->status === PublishStatus::Published
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }
}
