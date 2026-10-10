<?php

use App\Models\Category;
use App\Models\Experience;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Post;
use App\Models\ProcessStep;
use App\Models\Project;
use App\Models\ProjectScreen;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Support\TipTapDocument;

it('lists published categories with published post counts', function () {
    $laravel = Category::factory()->create(['name' => 'Laravel', 'sort_order' => 1]);
    Category::factory()->create(['name' => 'Hidden'])->update(['status' => 'draft']);
    Post::factory()->count(2)->for($laravel)->create();
    Post::factory()->for($laravel)->draft()->create();

    $response = api('categories')->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0'))->toMatchArray(['slug' => 'laravel', 'posts_count' => 2]);

    api('categories/laravel')->assertOk()->assertJsonPath('data.name', 'Laravel');
    api('categories/hidden')->assertNotFound();
});

it('lists projects and filters featured ones', function () {
    Project::factory()->create(['featured' => true, 'title' => 'Featured one', 'stack' => ['Laravel', 'Next.js', 'Docker']]);
    Project::factory()->create(['featured' => false]);
    Project::factory()->draft()->create();

    api('projects')->assertJsonCount(2, 'data');
    $featured = api('projects?featured=1')->assertJsonCount(1, 'data');
    expect($featured->json('data.0.tags'))->toBe(['Laravel', 'Next.js']);
    expect($featured->json('data.0.stack'))->toBe(['Laravel', 'Next.js', 'Docker']);
});

it('serves the projects list page copy', function () {
    // The row exists already: a migration adds it to every site.
    Page::query()->where('key', 'projects')->firstOrFail()->update(['title' => 'Projects', 'content' => ['eyebrow' => 'PROJECTS', 'description' => 'All work']]);

    api('pages/projects')->assertOk()->assertJsonPath('data.title', 'Projects')->assertJsonPath('data.content.eyebrow', 'PROJECTS');
});

it('shows a project with rich sections, screens, quote and a looping next project', function () {
    $testimonial = Testimonial::factory()->create(['name' => 'Khalid']);
    $service = Service::factory()->create();
    $first = Project::factory()->create(['sort_order' => 1, 'testimonial_id' => $testimonial->id, 'service_id' => $service->id, 'challenge' => TipTapDocument::fromParagraphs(['Old store']), 'live_url' => null]);
    $second = Project::factory()->create(['sort_order' => 2]);
    ProjectScreen::factory()->for($first)->create(['caption' => 'Checkout', 'sort_order' => 1]);

    $response = api("projects/{$first->slug}")->assertOk()
        ->assertJsonPath('data.live_url', null)
        ->assertJsonPath('data.testimonial.name', 'Khalid')
        ->assertJsonPath('data.service.slug', $service->slug)
        ->assertJsonPath('data.next.slug', $second->slug)
        ->assertJsonPath('data.screens.0.caption', 'Checkout');
    expect($response->json('data.challenge_html'))->toBe('<p>Old store</p>');

    api("projects/{$second->slug}")->assertJsonPath('data.next.slug', $first->slug);
});

it('returns null for empty case-study sections and 404 for drafts', function () {
    $project = Project::factory()->create(['challenge' => null, 'solution' => null, 'result' => null]);
    $draft = Project::factory()->draft()->create();

    api("projects/{$project->slug}")->assertJsonPath('data.challenge_html', null)->assertJsonPath('data.result_html', null);
    api("projects/{$draft->slug}")->assertNotFound();
    api('projects/unknown')->assertNotFound();
});

it('lists testimonials with an optional featured filter', function () {
    Testimonial::factory()->create(['featured' => true, 'sort_order' => 1]);
    Testimonial::factory()->create(['featured' => false, 'sort_order' => 2]);

    api('testimonials')->assertJsonCount(2, 'data')->assertJsonStructure(['data' => [['quote', 'name', 'role', 'company', 'initials', 'rating', 'avatar']]]);
    api('testimonials?featured=1')->assertJsonCount(1, 'data');
});

it('lists services and shows one with related content and posts', function () {
    $category = Category::factory()->create();
    $project = Project::factory()->create();
    $service = Service::factory()->create(['related_project_id' => $project->id, 'related_category_id' => $category->id]);
    Service::factory()->draft()->create();
    Post::factory()->count(4)->for($category)->create();
    Post::factory()->create();

    api('services')->assertJsonCount(1, 'data')->assertJsonStructure(['data' => [['slug', 'nav_label', 'title', 'icon', 'lead']]]);

    $response = api("services/{$service->slug}")->assertOk()
        ->assertJsonPath('data.related_project.slug', $project->slug)
        ->assertJsonPath('data.related_category.slug', $category->slug)
        ->assertJsonCount(3, 'data.posts')
        ->assertJsonStructure(['data' => ['h1', 'pains', 'offers', 'why' => ['title', 'text_html', 'points'], 'stack', 'tiers', 'faq', 'seo']]);
    expect(collect($response->json('data.posts'))->pluck('category.slug')->unique()->all())->toBe([$category->slug]);
});

it('prefers manually chosen related posts on a service', function () {
    $service = Service::factory()->create();
    $chosen = Post::factory()->create();
    Post::factory()->count(3)->create();
    $service->posts()->attach($chosen->id, ['sort_order' => 1]);

    api("services/{$service->slug}")->assertJsonCount(1, 'data.posts')->assertJsonPath('data.posts.0.slug', $chosen->slug);
});

it('returns 404 for draft and unknown services', function () {
    $draft = Service::factory()->draft()->create();

    api("services/{$draft->slug}")->assertNotFound();
    api('services/unknown')->assertNotFound();
});

it('lists experiences newest first, process steps in order and faqs by scope', function () {
    Experience::factory()->create(['role' => 'Older', 'start_year' => 2020, 'end_year' => 2022]);
    Experience::factory()->create(['role' => 'Current', 'start_year' => 2024, 'end_year' => null]);
    ProcessStep::factory()->create(['title' => 'Second', 'sort_order' => 2]);
    ProcessStep::factory()->create(['title' => 'First', 'sort_order' => 1]);
    Faq::factory()->create(['question' => 'Q2?', 'sort_order' => 2, 'answer' => TipTapDocument::fromParagraphs(['Answer'])]);
    Faq::factory()->create(['question' => 'Q1?', 'sort_order' => 1]);
    Faq::factory()->create(['scope' => 'other']);

    api('experiences')->assertJsonPath('data.0.role', 'Current')->assertJsonPath('data.0.end_year', null)->assertJsonPath('data.1.role', 'Older');
    api('process-steps')->assertJsonPath('data.0.title', 'First');
    $faqs = api('faqs?scope=contact')->assertJsonCount(2, 'data')->assertJsonPath('data.0.question', 'Q1?');
    expect($faqs->json('data.1.answer_html'))->toBe('<p>Answer</p>');
    api('faqs')->assertJsonCount(2, 'data');
});

it('returns site settings without secrets', function () {
    Setting::factory()->create(['brand_name' => 'Ali Codes', 'ga_measurement_id' => 'G-TEST123', 'socials' => ['github' => 'https://github.com/x', 'x' => null]]);

    $response = api('settings')->assertOk()
        ->assertJsonPath('data.brand.name', 'Ali Codes')
        ->assertJsonPath('data.tracking.ga_measurement_id', 'G-TEST123')
        ->assertJsonPath('data.socials', ['github' => 'https://github.com/x'])
        ->assertJsonPath('data.contact_options.upload.max_files', 5)
        ->assertJsonStructure(['data' => ['brand', 'profile', 'contact', 'socials', 'stats', 'popular_searches', 'contact_options', 'tracking', 'site_noindex', 'seo']]);

    expect(json_encode($response->json()))->not->toContain('internal')->not->toContain('password');
});

it('serves fixed pages with html content and 404s for unknown keys', function () {
    Page::factory()->create(['key' => 'about', 'title' => 'About', 'content' => ['hero' => ['text' => TipTapDocument::fromParagraphs(['Hello there']), 'chip' => 'ABOUT ME']]]);
    Page::factory()->create(['key' => 'privacy', 'title' => 'Privacy', 'body' => TipTapDocument::fromParagraphs(['Policy text'])]);

    api('pages/about')->assertOk()->assertJsonPath('data.content.hero.text', '<p>Hello there</p>')->assertJsonPath('data.content.hero.chip', 'ABOUT ME');
    api('pages/privacy')->assertJsonPath('data.body_html', '<p>Policy text</p>');
    api('pages/unknown')->assertNotFound();
});

it('builds a sitemap without hidden or draft content', function () {
    $category = Category::factory()->create();
    Post::factory()->count(2)->for($category)->create();
    Post::factory()->for($category)->create(['noindex' => true]);
    Post::factory()->for($category)->draft()->create();
    Project::factory()->create();
    Project::factory()->create(['noindex' => true]);
    Service::factory()->create();
    Page::factory()->create(['key' => 'home']);
    Page::factory()->create(['key' => 'not_found']);

    $data = api('sitemap')->assertOk()->json('data');

    expect($data['posts'])->toHaveCount(2)
        ->and($data['projects'])->toHaveCount(1)
        ->and($data['services'])->toHaveCount(1)
        ->and($data['categories'][0]['posts_count'])->toBe(3)
        // /projects comes from the migration that adds that page to existing sites.
        ->and(collect($data['pages'])->pluck('path')->all())->toEqualCanonicalizing(['/', '/projects'])
        ->and($data['posts_per_page'])->toBe(6);
});
