<?php

use App\Jobs\RevalidateFrontend;
use App\Models\Category;
use App\Models\Post;
use App\Models\Redirect;
use App\Models\Service;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    // Without a secret the observers never queue a job, so slug/redirect tests stay offline.
    config(['portfolio.revalidate_secret' => '', 'portfolio.next_url' => 'http://next.test']);
    Cache::flush();
});

it('does nothing when no revalidate secret is configured', function () {
    config(['portfolio.revalidate_secret' => '']);
    Bus::fake();

    Post::factory()->create();

    Bus::assertNotDispatched(RevalidateFrontend::class);
});

it('collects the tags of one save into a single delayed job', function () {
    config(['portfolio.revalidate_secret' => 'secret']);
    Bus::fake();

    $post = Post::factory()->create(['slug' => 'first']);
    $post->update(['title' => 'Changed']);

    Bus::assertDispatchedTimes(RevalidateFrontend::class, 1);
    expect(Cache::get('revalidate:pending'))->toContain('posts', 'post:first');
});

it('posts the pending tags to Next.js with the bearer secret', function () {
    config(['portfolio.revalidate_secret' => 'secret']);
    Http::fake(['next.test/*' => Http::response(['revalidated' => []])]);
    Cache::put('revalidate:pending', ['posts', 'post:a'], 60);

    (new RevalidateFrontend)->handle();

    Http::assertSent(fn (Request $request) => $request->url() === 'http://next.test/api/revalidate'
        && $request->hasHeader('Authorization', 'Bearer secret')
        && $request['tags'] === ['posts', 'post:a']);
    expect(Cache::get('revalidate:pending'))->toBeNull();
});

it('keeps the tags for the retry when Next.js is unreachable', function () {
    config(['portfolio.revalidate_secret' => 'secret']);
    Http::fake(['next.test/*' => Http::response('nope', 500)]);
    Cache::put('revalidate:pending', ['posts'], 60);

    expect(fn () => (new RevalidateFrontend)->handle())->toThrow(Exception::class);
    expect(Cache::get('revalidate:pending'))->toBe(['posts']);
});

it('retries three times with backoff', function () {
    $job = new RevalidateFrontend;

    expect($job->tries)->toBe(3)->and($job->backoff())->toBe([5, 30, 120]);
});

it('redirects the old path when a slug changes', function () {
    $post = Post::factory()->create(['slug' => 'old-name']);
    $post->update(['slug' => 'new-name']);
    $post->update(['slug' => 'newest-name']);

    expect(Redirect::pluck('to_path', 'from_path')->all())->toBe([
        '/blog/old-name' => '/blog/newest-name',
        '/blog/new-name' => '/blog/newest-name',
    ]);
});

it('also revalidates the page cached under the old slug', function () {
    config(['portfolio.revalidate_secret' => 'secret']);
    Bus::fake();
    $post = Post::factory()->create(['slug' => 'before']);
    $post->update(['slug' => 'after']);

    expect(Cache::get('revalidate:pending'))->toContain('post:before', 'post:after', 'redirects');
});

it('drops a redirect that would point at itself when a slug is reused', function () {
    $post = Post::factory()->create(['slug' => 'a']);
    $post->update(['slug' => 'b']);
    $post->update(['slug' => 'a']);

    expect(Redirect::where('from_path', '/blog/a')->exists())->toBeFalse()
        ->and(Redirect::where('from_path', '/blog/b')->value('to_path'))->toBe('/blog/a');
});

it('tracks services and categories under their own paths', function () {
    Service::factory()->create(['slug' => 's1'])->update(['slug' => 's2']);
    Category::factory()->create(['slug' => 'c1'])->update(['slug' => 'c2']);

    expect(Redirect::pluck('to_path', 'from_path')->all())->toMatchArray([
        '/services/s1' => '/services/s2',
        '/blog/category/c1' => '/blog/category/c2',
    ]);
});

it('resolves redirects through the API', function () {
    Redirect::create(['from_path' => '/blog/old', 'to_path' => '/blog/new', 'status_code' => 301]);
    $headers = ['X-Internal-Key' => config('portfolio.internal_key')];

    $this->getJson('/api/v1/redirects?path=/blog/old', $headers)
        ->assertOk()->assertJson(['data' => ['to' => '/blog/new', 'status' => 301]]);
    $this->getJson('/api/v1/redirects?path=/blog/none', $headers)->assertNotFound();
    $this->getJson('/api/v1/redirects?path=/blog/old')->assertUnauthorized();
});
