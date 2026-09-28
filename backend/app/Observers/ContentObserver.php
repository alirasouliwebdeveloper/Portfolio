<?php

namespace App\Observers;

use App\Support\ContentCache;
use App\Support\ContentTags;
use Illuminate\Database\Eloquent\Model;

/** Clears the cached API responses that depend on a model whenever it changes. */
class ContentObserver
{
    public function saved(Model $model): void
    {
        ContentCache::flush(ContentTags::for($model));
    }

    public function deleted(Model $model): void
    {
        ContentCache::flush(ContentTags::for($model));
    }
}
