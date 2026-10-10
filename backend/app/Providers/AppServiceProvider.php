<?php

namespace App\Providers;

use App\Listeners\StoreImageDimensions;
use App\Models\Category;
use App\Models\Experience;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Post;
use App\Models\ProcessStep;
use App\Models\Project;
use App\Models\ProjectScreen;
use App\Models\Redirect;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\Testimonial;
use App\Observers\ContentObserver;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentTimezone;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        foreach ([Post::class, Category::class, Tag::class, Project::class, ProjectScreen::class, Testimonial::class, Service::class, Setting::class, Page::class, Experience::class, ProcessStep::class, Faq::class, Redirect::class, Media::class] as $model) {
            $model::observe(ContentObserver::class);
        }

        Event::listen(MediaHasBeenAddedEvent::class, StoreImageDimensions::class);

        FilamentTimezone::set(config('portfolio.display_timezone'));

        // Public write endpoints: 20 uploads and 5 contact messages per hour per visitor.
        RateLimiter::for('uploads', fn (Request $request) => Limit::perHour(20)->by($request->ip()));
        RateLimiter::for('contact', fn (Request $request) => Limit::perHour(5)->by($request->input('visitor_ip') ?: $request->ip()));

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
