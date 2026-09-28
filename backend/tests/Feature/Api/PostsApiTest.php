<?php

use App\Models\Category;
use App\Models\Post;
use App\Support\TipTapDocument;

it('lists only visible posts newest first with the pagination envelope', function () {
    $category = Category::factory()->create();
    $old = Post::factory()->for($category)->create(['published_at' => now()->subDays(3)]);
    $new = Post::factory()->for($category)->create(['published_at' => now()->subDay()]);
    Post::factory()->for($category)->draft()->create();
    Post::factory()->for($category)->scheduled()->create();
    Post::factory()->for(Category::factory()->draft())->create();

    api('posts')->assertOk()
        ->assertJsonPath('meta.total', 2)
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.last_page', 1)
        ->assertJsonPath('meta.per_page', 6)
        ->assertJsonPath('data.0.slug', $new->slug)
        ->assertJsonPath('data.1.slug', $old->slug)
        ->assertJsonStructure(['data' => [['slug', 'title', 'excerpt', 'cover', 'category' => ['name', 'slug'], 'published_at', 'reading_time', 'featured']], 'meta' => ['current_page', 'last_page', 'per_page', 'total', 'from', 'to']]);
});

it('paginates six posts per page', function () {
    Post::factory()->count(7)->create();

    api('posts')->assertJsonCount(6, 'data')->assertJsonPath('meta.last_page', 2)->assertJsonPath('meta.total', 7);
    api('posts?page=2')->assertJsonCount(1, 'data')->assertJsonPath('meta.from', 7);
});

it('validates per_page', function () {
    api('posts?per_page=500')->assertUnprocessable();
});

it('filters by category', function () {
    $laravel = Category::factory()->create(['name' => 'Laravel']);
    Post::factory()->count(2)->for($laravel)->create();
    Post::factory()->create();

    api('posts?category=laravel')->assertJsonPath('meta.total', 2);
});

it('returns the newest featured post and can exclude it from the list', function () {
    $category = Category::factory()->create();
    Post::factory()->for($category)->featured()->create(['title' => 'Old featured', 'published_at' => now()->subDays(5)]);
    $featured = Post::factory()->for($category)->featured()->create(['title' => 'Newest featured', 'published_at' => now()->subDay()]);
    Post::factory()->for($category)->create();

    api('posts/featured')->assertJsonPath('data.slug', $featured->slug);
    api('posts?exclude_featured=1')->assertJsonPath('meta.total', 2)
        ->assertJsonMissing(['slug' => $featured->slug]);
    api('posts/featured?category=nope')->assertJsonPath('data', null);
});

it('shows a post with html body, toc, related posts and neighbours', function () {
    $category = Category::factory()->create();
    $doc = ['type' => 'doc', 'content' => [
        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'The problem']]],
        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Some text']]],
        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'The problem']]],
    ]];
    $older = Post::factory()->for($category)->create(['published_at' => now()->subDays(4)]);
    $post = Post::factory()->for($category)->create(['body' => $doc, 'published_at' => now()->subDays(2), 'meta_title' => 'Custom SEO title']);
    $newer = Post::factory()->for($category)->create(['published_at' => now()->subDay()]);

    $response = api("posts/{$post->slug}")->assertOk()
        ->assertJsonPath('data.slug', $post->slug)
        ->assertJsonPath('data.previous.slug', $older->slug)
        ->assertJsonPath('data.next.slug', $newer->slug)
        ->assertJsonPath('data.seo.meta_title', 'Custom SEO title')
        ->assertJsonPath('data.seo.meta_description', $post->excerpt)
        ->assertJsonPath('data.toc.0.id', 'the-problem')
        ->assertJsonPath('data.toc.1.id', 'the-problem-2')
        ->assertJsonCount(2, 'data.related');

    expect($response->json('data.body_html'))->toContain('<h2 id="the-problem">')->toContain('<p>Some text</p>');
    expect(collect($response->json('data.related'))->pluck('slug'))->not->toContain($post->slug);
});

it('returns 404 for draft, scheduled and unknown posts', function () {
    $draft = Post::factory()->draft()->create();
    $scheduled = Post::factory()->scheduled()->create();

    api("posts/{$draft->slug}")->assertNotFound();
    api("posts/{$scheduled->slug}")->assertNotFound();
    api('posts/does-not-exist')->assertNotFound();
});

it('sanitizes dangerous html in the body', function () {
    $doc = TipTapDocument::fromParagraphs(['Hello']);
    $post = Post::factory()->create(['body' => $doc]);
    Post::query()->whereKey($post)->update(['body' => json_encode(['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => '<script>alert(1)</script>']]]]])]);

    expect(api("posts/{$post->slug}")->json('data.body_html'))->not->toContain('<script>');
});
