<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image;
use Throwable;

/**
 * Re-encodes images attached inside the rich editor to WebP (max 2000px) before
 * they are stored, so pages never ship a multi-megabyte original.
 */
final class ImageOptimizer
{
    private const MAX_DIMENSION = 2000;

    private const QUALITY = 82;

    public static function store(
        TemporaryUploadedFile $file,
        ?string $directory,
        string $disk,
        ?string $visibility = null,
    ): ?string {
        if (! $file->exists()) {
            return null;
        }

        $filesystem = Storage::disk($disk);
        $path = trim(($directory ?? '').'/'.Str::uuid().'.webp', '/');
        $temp = tempnam(sys_get_temp_dir(), 'img');

        try {
            Image::useImageDriver(config('media-library.image_driver', 'gd'))
                ->loadFile($file->getRealPath())
                ->fit(Fit::Max, self::MAX_DIMENSION, self::MAX_DIMENSION)
                ->format('webp')
                ->quality(self::QUALITY)
                ->save($temp);

            $filesystem->put($path, (string) file_get_contents($temp), $visibility ? ['visibility' => $visibility] : []);

            return $path;
        } catch (Throwable $exception) {
            report($exception);

            return $file->store($directory ?? '', ['disk' => $disk, 'visibility' => $visibility]);
        } finally {
            @unlink($temp);
        }
    }
}
