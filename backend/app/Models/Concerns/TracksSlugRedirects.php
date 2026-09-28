<?php

namespace App\Models\Concerns;

use App\Jobs\RevalidateFrontend;
use App\Models\Redirect;
use App\Support\ContentCache;

/**
 * Keeps old URLs alive: when a slug changes, the old public path is redirected to the new one
 * (existing redirects into the old path are re-pointed, so there are never chains or loops).
 * The model defines `publicPath(string $slug): string`.
 */
trait TracksSlugRedirects
{
    public static function bootTracksSlugRedirects(): void
    {
        static::updated(function ($model): void {
            if (! $model->wasChanged('slug') || blank($model->getOriginal('slug'))) {
                return;
            }

            $old = $model->publicPath($model->getOriginal('slug'));
            $new = $model->publicPath($model->slug);

            Redirect::where('from_path', $new)->delete();
            Redirect::where('to_path', $old)->update(['to_path' => $new]);
            Redirect::updateOrCreate(['from_path' => $old], ['to_path' => $new, 'status_code' => 301]);

            // The old URL's cached page must stop being served, and the redirect must be visible.
            $tags = ['redirects', strtolower(class_basename($model)).':'.$model->getOriginal('slug')];
            ContentCache::flush($tags);
            RevalidateFrontend::schedule($tags);
        });
    }
}
