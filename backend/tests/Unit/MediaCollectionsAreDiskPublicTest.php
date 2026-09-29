<?php

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\ProjectScreen;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Testimonial;
use Spatie\MediaLibrary\HasMedia;

/**
 * A media collection without an explicit `useDisk('public')` silently falls back to
 * `config('filament.default_filesystem_disk')` (= 'local') when a file is uploaded through the
 * admin, producing a URL nothing can reach — it happened to the site logo. Every model here is
 * public-facing, so every collection it registers must be pinned to the public disk.
 */
it('registers every media collection on the public disk', function (string $class) {
    /** @var HasMedia $model */
    $model = new $class;

    $collections = $model->getRegisteredMediaCollections();

    expect($collections)->not->toBeEmpty();

    foreach ($collections as $collection) {
        expect($collection->diskName)
            ->toBe('public', "{$class}'s '{$collection->name}' collection is not pinned to the public disk.");
    }
})->with([
    Category::class,
    Page::class,
    Post::class,
    Project::class,
    ProjectScreen::class,
    Service::class,
    Setting::class,
    Testimonial::class,
]);
