<?php

use App\Enums\PublishStatus;
use App\Models\Project;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    config(['portfolio.revalidate_secret' => 'secret', 'portfolio.next_url' => 'http://next.test']);
    Cache::flush();
    // Keep the revalidation job queued so the tags it would send can be inspected.
    Bus::fake();
});

it('refreshes content whose scheduled publish time has arrived', function () {
    $this->travelTo(now()->subHour());
    Project::factory()->create(['slug' => 'later', 'status' => PublishStatus::Published, 'published_at' => now()->addMinutes(30)]);
    $this->artisan('content:publish-scheduled')->assertSuccessful();
    Cache::forget('revalidate:pending');
    $this->travelBack();

    $this->artisan('content:publish-scheduled')->assertSuccessful();

    expect(Cache::get('revalidate:pending'))->toContain('projects', 'project:later', 'sitemap');
});

it('ignores content that is still scheduled or already live before the last run', function () {
    Project::factory()->create(['slug' => 'future', 'status' => PublishStatus::Published, 'published_at' => now()->addDay()]);
    Cache::forever('content:publish-scheduled:last-run', now()->toIso8601String());
    Project::withoutEvents(fn () => Project::factory()->create(['slug' => 'old', 'status' => PublishStatus::Published, 'published_at' => now()->subDay()]));
    Cache::forget('revalidate:pending');

    $this->artisan('content:publish-scheduled')->assertSuccessful();

    expect(Cache::get('revalidate:pending'))->toBeNull();
});
