<?php

namespace App\Observers;

use App\Jobs\RevalidateFrontend;
use App\Support\ContentCache;
use App\Support\ContentTags;
use Illuminate\Database\Eloquent\Model;

/** Clears the cached API responses that depend on a model and asks Next.js to refresh the same tags. */
class ContentObserver
{
    public function saved(Model $model): void
    {
        $this->refresh($model);
    }

    public function deleted(Model $model): void
    {
        $this->refresh($model);
    }

    private function refresh(Model $model): void
    {
        $tags = ContentTags::for($model);

        ContentCache::flush($tags);
        RevalidateFrontend::schedule($tags);
    }
}
