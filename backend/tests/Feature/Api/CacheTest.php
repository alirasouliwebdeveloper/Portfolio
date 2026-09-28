<?php

use App\Models\Category;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;

it('caches responses and clears them when content changes', function () {
    expect(Cache::supportsTags())->toBeTrue();

    $post = Post::factory()->for(Category::factory()->create())->create(['title' => 'Before']);

    expect(api('posts')->json('data.0.title'))->toBe('Before');

    // A raw update bypasses the observers, so the stale cached page proves the response was cached.
    Post::query()->whereKey($post)->update(['title' => 'Changed behind the cache']);
    expect(api('posts')->json('data.0.title'))->toBe('Before');

    // Saving through Eloquent fires the observer and flushes the tagged entries.
    $post->fresh()->update(['title' => 'After']);
    expect(api('posts')->json('data.0.title'))->toBe('After');
});

it('clears related caches when a category is renamed', function () {
    $category = Category::factory()->create(['name' => 'Old name']);
    Post::factory()->for($category)->create();

    expect(api('categories')->json('data.0.name'))->toBe('Old name');

    $category->update(['name' => 'New name']);

    expect(api('categories')->json('data.0.name'))->toBe('New name');
});

it('keeps different query strings in separate cache entries', function () {
    Post::factory()->count(7)->create();

    expect(api('posts?page=1')->json('data'))->toHaveCount(6)
        ->and(api('posts?page=2')->json('data'))->toHaveCount(1);
});
