<?php

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;

/**
 * Every publishable model defaults to 'en'; a Persian row is a separate row with
 * `locale = 'fa'` and `translation_of_id` pointing at its English original
 * (see the add_locale_support migration and docs/02-architecture.md, "Locales").
 */
beforeEach(function () {
    $this->headers = ['X-Internal-Key' => config('portfolio.internal_key')];
});

it('defaults every content list to English and only returns Persian rows when asked', function () {
    $category = Category::factory()->create();
    $enPost = Post::factory()->for($category)->create(['locale' => 'en']);
    $faPost = Post::factory()->for($category)->create(['locale' => 'fa', 'translation_of_id' => $enPost->id]);

    $default = $this->getJson('/api/v1/posts', $this->headers)->json('data.*.slug');
    expect($default)->toContain($enPost->slug)->not->toContain($faPost->slug);

    $fa = $this->getJson('/api/v1/posts?locale=fa', $this->headers)->json('data.*.slug');
    expect($fa)->toContain($faPost->slug)->not->toContain($enPost->slug);
});

it('ignores an unsupported locale value and falls back to English', function () {
    Post::factory()->for(Category::factory())->create(['slug' => 'en-post']);

    $data = $this->getJson('/api/v1/posts?locale=xx', $this->headers)->json('data.*.slug');

    expect($data)->toContain('en-post');
});

it('falls back to the English page when a translation does not exist yet', function () {
    $page = Page::factory()->create(['key' => 'about', 'locale' => 'en', 'title' => 'About']);

    $response = $this->getJson('/api/v1/pages/about?locale=fa', $this->headers);

    $response->assertOk()->assertJsonPath('data.title', 'About');
});

it('serves the Persian page once one exists for that key', function () {
    Page::factory()->create(['key' => 'about', 'locale' => 'en', 'title' => 'About']);
    Page::factory()->create(['key' => 'about', 'locale' => 'fa', 'title' => 'درباره من']);

    $this->getJson('/api/v1/pages/about?locale=fa', $this->headers)
        ->assertOk()->assertJsonPath('data.title', 'درباره من');

    $this->getJson('/api/v1/pages/about', $this->headers)
        ->assertOk()->assertJsonPath('data.title', 'About');
});

it('falls back Settings prose fields to English when no Persian value is set', function () {
    Setting::query()->delete();
    Setting::factory()->create(['headline' => 'Full-stack developer', 'headline_fa' => null]);

    $this->getJson('/api/v1/settings?locale=fa', $this->headers)
        ->assertJsonPath('data.profile.headline', 'Full-stack developer');
});

it('returns the Persian Settings prose fields once they are filled in', function () {
    Setting::query()->delete();
    Setting::factory()->create(['headline' => 'Full-stack developer', 'headline_fa' => 'توسعه‌دهنده full-stack']);

    $this->getJson('/api/v1/settings?locale=fa', $this->headers)
        ->assertJsonPath('data.profile.headline', 'توسعه‌دهنده full-stack');

    $this->getJson('/api/v1/settings', $this->headers)
        ->assertJsonPath('data.profile.headline', 'Full-stack developer');
});
