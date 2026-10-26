<?php

use App\Services\DeployRunner;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

it('has no HTTP deploy trigger; deploys only run from the deploy:check cron', function () {
    $this->post('/deploy-hook')->assertNotFound();
});

it('skips deploy:check when the release is already deployed', function () {
    $marker = storage_path('app/last_deployed_release_id.txt');
    file_put_contents($marker, '42');

    $runner = $this->mock(DeployRunner::class);
    $runner->shouldReceive('latestRelease')->andReturn(['id' => 42, 'published_at' => now()->toIso8601String()]);
    $runner->shouldNotReceive('run');

    $this->artisan('deploy:check')->assertSuccessful();

    @unlink($marker);
});

it('reports a GitHub connection failure instead of crashing', function () {
    Http::fake(fn () => throw new ConnectionException('cURL error 60: SSL certificate problem'));

    $results = app(DeployRunner::class)->run();

    expect($results['backend'])->toBe(['ok' => false, 'error' => 'release lookup failed: cURL error 60: SSL certificate problem'])
        ->and(app(DeployRunner::class)->latestRelease())->toBeNull();
});

it('removes only the stray source files from the frontend folder', function () {
    $dir = storage_path('framework/testing/frontend-'.uniqid());
    foreach (['src/app/page.tsx', 'tests/e2e/a.spec.ts', 'README.md', 'tsconfig.json', 'server.js', 'package.json', 'public/me.jpg', '.next/BUILD_ID', '.htaccess', 'tmp/restart.txt'] as $file) {
        @mkdir(dirname("{$dir}/{$file}"), 0755, true);
        file_put_contents("{$dir}/{$file}", 'x');
    }

    $method = new ReflectionMethod(DeployRunner::class, 'removeStrayFrontendFiles');
    $method->invoke(app(DeployRunner::class), $dir);

    foreach (['src', 'tests', 'README.md', 'tsconfig.json'] as $gone) {
        expect(file_exists("{$dir}/{$gone}"))->toBeFalse();
    }
    foreach (['server.js', 'package.json', 'public/me.jpg', '.next/BUILD_ID', '.htaccess', 'tmp/restart.txt'] as $kept) {
        expect(file_exists("{$dir}/{$kept}"))->toBeTrue();
    }

    File::deleteDirectory($dir);
});
