<?php

namespace App\Http\Resources\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/** Shared payload builders for the public API resources. */
trait BuildsPayloads
{
    /** @return array{url: string, card: string|null, md: string|null, lg: string|null, width: int|null, height: int|null, alt: string}|null */
    public static function image(?Media $media, ?string $alt = null): ?array
    {
        if (! $media) {
            return null;
        }

        $conversion = fn (string $name) => $media->hasGeneratedConversion($name) ? $media->getUrl($name) : null;

        return [
            'url' => $media->getUrl(),
            'card' => $conversion('card'),
            'md' => $conversion('md'),
            'lg' => $conversion('lg'),
            'width' => $media->getCustomProperty('width'),
            'height' => $media->getCustomProperty('height'),
            'alt' => (string) ($alt ?? ''),
        ];
    }

    public static function imageOf(HasMedia $model, string $collection, ?string $alt = null): ?array
    {
        return self::image($model->getFirstMedia($collection), $alt);
    }

    /**
     * SEO block with fallbacks: the frontend appends the site name only when `meta_title` is empty.
     *
     * @return array{meta_title: string|null, meta_description: string|null, canonical_url: string|null, noindex: bool, og_image: array|null}
     */
    public static function seo(Model&HasMedia $model, ?string $fallbackDescription = null): array
    {
        return [
            'meta_title' => filled($model->meta_title) ? $model->meta_title : null,
            'meta_description' => filled($model->meta_description) ? $model->meta_description : $fallbackDescription,
            'canonical_url' => filled($model->canonical_url ?? null) ? $model->canonical_url : null,
            'noindex' => (bool) ($model->noindex ?? false),
            'og_image' => self::imageOf($model, 'og_image'),
        ];
    }

    /** @return array{current_page: int, last_page: int, per_page: int, total: int, from: int|null, to: int|null} */
    public static function meta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }
}
