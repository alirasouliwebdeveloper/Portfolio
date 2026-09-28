<?php

namespace App\Models\Concerns;

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * WebP conversions in the sizes the frontend needs:
 * card (16:10 thumbnails), md (content width) and lg (full-width hero/case-study images).
 */
trait RegistersImageConversions
{
    use InteractsWithMedia;

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('card')
            ->fit(Fit::Crop, 800, 500)
            ->format('webp')
            ->performOnCollections(...$this->conversionCollections());

        $this->addMediaConversion('md')
            ->width(1280)
            ->format('webp')
            ->performOnCollections(...$this->conversionCollections());

        $this->addMediaConversion('lg')
            ->width(1920)
            ->format('webp')
            ->performOnCollections(...$this->conversionCollections());
    }

    /** @return list<string> */
    protected function conversionCollections(): array
    {
        return ['cover', 'hero_image', 'image', 'portrait', 'avatar', 'og_image'];
    }
}
