<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\Tag;
use Illuminate\Database\QueryException;

it('generates a kebab-case slug from the title', function () {
    $post = Post::factory()->create(['title' => 'Instant content updates with on-demand revalidation']);

    expect($post->slug)->toBe('instant-content-updates-with-on-demand-revalidation');
});

it('keeps slugs unique across records with the same title', function () {
    $first = Post::factory()->create(['title' => 'Same title']);
    $second = Post::factory()->create(['title' => 'Same title']);

    expect($first->slug)->not->toBe($second->slug)
        ->and(Post::query()->distinct()->count('slug'))->toBe(2);
});

it('keeps an explicit slug instead of generating one from the title', function () {
    $service = Service::factory()->create(['title' => 'Laravel Development Services', 'slug' => 'laravel-development']);

    expect($service->slug)->toBe('laravel-development');
});

it('does not change the public slug when the title is edited', function () {
    $project = Project::factory()->create(['title' => 'Original title']);
    $slug = $project->slug;

    $project->update(['title' => 'Completely different title']);

    expect($project->fresh()->slug)->toBe($slug);
});

it('enforces slug uniqueness in the database', function () {
    Category::factory()->create(['slug' => 'laravel']);

    expect(fn () => Category::factory()->create(['slug' => 'laravel']))->toThrow(QueryException::class);
});

it('generates unique tag slugs', function () {
    Tag::factory()->create(['name' => 'Next.js']);
    $duplicate = Tag::factory()->create(['name' => 'Next.js']);

    expect($duplicate->slug)->not->toBe('next-js');
});
