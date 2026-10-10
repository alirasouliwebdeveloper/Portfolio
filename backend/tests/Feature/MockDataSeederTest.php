<?php

use App\Models\Category;
use App\Models\Experience;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Post;
use App\Models\ProcessStep;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\User;
use App\Support\IconSet;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

// Seeding builds WebP conversions, so it is done once per test that needs it.
it('seeds all mock content, published and linked, and is idempotent', function () {
    Storage::fake('public');
    $this->seed(DatabaseSeeder::class);

    expect(Category::count())->toBe(6)
        ->and(Post::count())->toBe(16)
        ->and(Project::count())->toBe(3)
        ->and(Service::count())->toBe(5)
        ->and(Testimonial::count())->toBe(3)
        ->and(Experience::count())->toBe(3)
        ->and(ProcessStep::count())->toBe(4)
        ->and(Faq::count())->toBe(8)
        ->and(Setting::count())->toBe(1)
        ->and(Page::count())->toBe(8)
        ->and(Page::whereNull('seo_score')->count())->toBe(0);

    expect(Post::published()->count())->toBe(16)
        ->and(Project::published()->count())->toBe(3)
        ->and(Service::published()->count())->toBe(5)
        ->and(Category::published()->count())->toBe(6);

    expect(Service::pluck('slug')->sort()->values()->all())->toBe([
        'api-development',
        'laravel-development',
        'n8n-automation',
        'nextjs-website-development',
        'online-store-development',
    ]);

    expect(Post::where('featured', true)->count())->toBe(1)
        ->and(Post::where('slug', 'building-an-admin-panel-for-a-headless-site-with-laravel-and-filament')->value('reading_time'))->toBe(9);

    $service = Service::where('slug', 'laravel-development')->first();
    expect($service->relatedProject->slug)->toBe('e-commerce-platform')
        ->and($service->relatedCategory->slug)->toBe('laravel');

    expect(User::where('email', config('portfolio.admin.email'))->exists())->toBeTrue();

    $this->seed(DatabaseSeeder::class);
    expect(Post::count())->toBe(16);
});

it('attaches the mock images with webp conversions and project screens', function () {
    Storage::fake('public');
    $this->seed(DatabaseSeeder::class);

    $cover = Post::first()->getFirstMedia('cover');

    expect($cover)->not->toBeNull()
        ->and($cover->hasGeneratedConversion('card'))->toBeTrue()
        ->and($cover->getUrl('card'))->toEndWith('.webp')
        ->and(Setting::first()->getFirstMedia('portrait'))->not->toBeNull()
        ->and(Service::first()->getFirstMedia('hero_image'))->not->toBeNull()
        ->and(Project::where('slug', 'e-commerce-platform')->first()->screens)->toHaveCount(4)
        ->and(Media::whereNull('uuid')->count())->toBe(0);
});

it('uses the same icon names as the mock data', function () {
    $mock = json_decode(file_get_contents(config('portfolio.seed_path').'/mock-data.json'), true);

    expect(IconSet::NAMES)->toEqualCanonicalizing($mock['icons']);
});
