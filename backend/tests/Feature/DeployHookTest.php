<?php

use App\Services\DeployRunner;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

it('rejects deploy-hook calls without the shared secret', function () {
    config(['services.deploy.token' => 'secret']);

    $this->postJson('/deploy-hook')->assertForbidden();
    $this->postJson('/deploy-hook', [], ['X-Deploy-Token' => 'wrong'])->assertForbidden();
});

it('refuses every deploy-hook call when no secret is configured', function () {
    config(['services.deploy.token' => null]);

    $this->postJson('/deploy-hook', [], ['X-Deploy-Token' => ''])->assertForbidden();
});

it('runs the deploy with the right secret', function () {
    config(['services.deploy.token' => 'secret']);

    $this->mock(DeployRunner::class)
        ->shouldReceive('run')->once()
        ->andReturn(['backend' => ['ok' => true], 'frontend' => ['ok' => true]]);

    $this->postJson('/deploy-hook', [], ['X-Deploy-Token' => 'secret'])
        ->assertOk()
        ->assertJson(['ok' => true]);
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
    config(['services.deploy.token' => 'secret']);
    Http::fake(fn () => throw new ConnectionException('cURL error 60: SSL certificate problem'));

    $this->postJson('/deploy-hook', [], ['X-Deploy-Token' => 'secret'])
        ->assertStatus(500)
        ->assertJsonPath('results.backend.error', 'release lookup failed: cURL error 60: SSL certificate problem');
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
