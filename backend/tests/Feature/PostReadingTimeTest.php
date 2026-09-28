<?php

use App\Models\Post;

function words(int $count): string
{
    return '<p>'.implode(' ', array_fill(0, $count, 'word')).'</p>';
}

it('computes reading time as words divided by 220, rounded up', function (int $words, int $minutes) {
    expect(Post::readingTimeFor(words($words)))->toBe($minutes);
})->with([
    'tiny' => [10, 1],
    'exactly one minute' => [220, 1],
    'just over' => [221, 2],
    'ten minutes' => [2200, 10],
]);

it('ignores html tags when counting words', function () {
    expect(Post::readingTimeFor('<h2>Title</h2><pre><code>a b c</code></pre>'))->toBe(1);
});

it('sets the reading time when a post is saved', function () {
    $post = Post::factory()->create(['body' => words(660)]);

    expect($post->fresh()->reading_time)->toBe(3);
});

it('recalculates the reading time when the body changes', function () {
    $post = Post::factory()->create(['body' => words(220)]);
    $post->update(['body' => words(1100)]);

    expect($post->fresh()->reading_time)->toBe(5);
});
