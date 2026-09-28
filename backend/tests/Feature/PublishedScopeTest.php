<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;

dataset('publishable models', [
    'posts' => [fn () => Post::factory()],
    'projects' => [fn () => Project::factory()],
    'services' => [fn () => Service::factory()],
    'categories' => [fn () => Category::factory()],
]);

it('only returns published records whose publish date has passed', function (Closure $factory) {
    $published = $factory()->create();
    $factory()->draft()->create();

    $factoryInstance = $factory();
    if (method_exists($factoryInstance, 'scheduled')) {
        $factoryInstance->scheduled()->create();
    } else {
        $factoryInstance->state(['published_at' => now()->addDay()])->create();
    }

    $modelClass = $published::class;
    expect($modelClass::published()->pluck('id')->all())->toBe([$published->id]);
})->with('publishable models');

it('does not treat published status without a date as published', function () {
    $post = Post::factory()->create();
    Post::query()->whereKey($post)->update(['published_at' => null]);

    expect(Post::published()->count())->toBe(0);
});

it('sets published_at automatically when a record is saved as published without a date', function () {
    $post = Post::factory()->create(['published_at' => null]);

    expect($post->fresh()->published_at)->not->toBeNull()
        ->and($post->isPublished())->toBeTrue();
});

it('keeps drafts unpublished', function () {
    $post = Post::factory()->draft()->create();

    expect($post->published_at)->toBeNull()
        ->and($post->isPublished())->toBeFalse();
});
