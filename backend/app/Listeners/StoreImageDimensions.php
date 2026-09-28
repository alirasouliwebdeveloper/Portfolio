<?php

namespace App\Listeners;

use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

/** Stores width/height of uploaded images so the frontend can reserve space (no layout shift). */
class StoreImageDimensions
{
    public function handle(MediaHasBeenAddedEvent $event): void
    {
        $media = $event->media;

        if (! str_starts_with((string) $media->mime_type, 'image/') || $media->mime_type === 'image/svg+xml') {
            return;
        }

        $size = @getimagesize($media->getPath());

        if ($size) {
            $media->setCustomProperty('width', $size[0])->setCustomProperty('height', $size[1])->save();
        }
    }
}
