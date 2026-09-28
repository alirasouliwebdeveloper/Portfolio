<?php

namespace App\Providers;

use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Rich-editor additions (font size, direction, image/link attributes). `filament:assets`
        // copies this file to public/js/app; the version is its hash so edits bust browser caches.
        $editorScript = resource_path('js/filament/rich-content-plugins/site-wp.js');

        FilamentAsset::register([
            Js::make('rich-content-plugins/site-wp', $editorScript)->loadedOnRequest(),
        ]);

        if (is_file($editorScript)) {
            FilamentAsset::appVersion(substr((string) md5_file($editorScript), 0, 10));
        }
    }
}
