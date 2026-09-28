<?php

use App\Models\Category;
use App\Models\Post;
use App\Support\TipTapDocument;

it('requires a query of 2 to 100 characters', function () {
    api('search')->assertUnprocessable();
    api('search?q=a')->assertUnprocessable();
    api('search?q='.str_repeat('x', 101))->assertUnprocessable();
});

it('finds posts by title, excerpt and body and ranks title matches first', function () {
    $category = Category::factory()->create();
    $body = Post::factory()->for($category)->create(['title' => 'Unrelated', 'excerpt' => 'Nothing here', 'body' => TipTapDocument::fromParagraphs(['We discuss Filament deeply']), 'published_at' => now()]);
    $excerpt = Post::factory()->for($category)->create(['title' => 'Other', 'excerpt' => 'A Filament excerpt', 'published_at' => now()->subDay()]);
    $title = Post::factory()->for($category)->create(['title' => 'Filament in production', 'published_at' => now()->subDays(2)]);
    Post::factory()->for($category)->create(['title' => 'Nothing to see']);

    $response = api('search?q=filament')->assertOk()->assertJsonPath('meta.total', 3)->assertJsonPath('meta.query', 'filament');

    expect(collect($response->json('data'))->pluck('slug')->all())->toBe([$title->slug, $excerpt->slug, $body->slug]);
});

it('sorts by newest when asked', function () {
    $category = Category::factory()->create();
    $old = Post::factory()->for($category)->create(['title' => 'Docker old', 'published_at' => now()->subDays(5)]);
    $new = Post::factory()->for($category)->create(['excerpt' => 'about docker', 'published_at' => now()->subDay()]);

    expect(collect(api('search?q=docker&sort=newest')->json('data'))->pluck('slug')->all())->toBe([$new->slug, $old->slug]);
});

it('returns per-category counts and can filter by category', function () {
    $laravel = Category::factory()->create(['name' => 'Laravel']);
    $devops = Category::factory()->create(['name' => 'DevOps']);
    Post::factory()->count(2)->for($laravel)->create(['title' => 'Queues explained']);
    Post::factory()->for($devops)->create(['title' => 'Queues in Docker']);

    $response = api('search?q=queues')->assertJsonPath('meta.total_all', 3);
    expect(collect($response->json('categories'))->pluck('count', 'slug')->all())->toBe(['laravel' => 2, 'devops' => 1]);

    api('search?q=queues&category=devops')->assertJsonPath('meta.total', 1)->assertJsonPath('meta.total_all', 3);
});

it('never returns drafts and treats wildcard characters literally', function () {
    Post::factory()->draft()->create(['title' => 'Secret laravel draft']);
    Post::factory()->create(['title' => 'Public 100% guide']);

    api('search?q=laravel')->assertJsonPath('meta.total', 0);
    api('search?q=100%25')->assertJsonPath('meta.total', 1);
    api('search?q=%25%25')->assertJsonPath('meta.total', 0);
});

it('paginates results', function () {
    Post::factory()->count(8)->create(['title' => 'Matching title']);

    api('search?q=matching')->assertJsonCount(6, 'data')->assertJsonPath('meta.last_page', 2);
    api('search?q=matching&page=2')->assertJsonCount(2, 'data');
});
