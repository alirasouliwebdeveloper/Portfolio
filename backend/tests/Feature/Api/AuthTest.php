<?php

beforeEach(fn () => config(['portfolio.internal_key' => 'test-key']));

it('rejects requests without the internal key', function () {
    $this->getJson('/api/v1/settings')->assertUnauthorized();
});

it('rejects requests with a wrong key', function () {
    $this->getJson('/api/v1/settings', ['X-Internal-Key' => 'nope'])->assertUnauthorized();
});

it('never opens the api when no key is configured', function () {
    config(['portfolio.internal_key' => '']);

    $this->getJson('/api/v1/settings', ['X-Internal-Key' => ''])->assertUnauthorized();
});

it('accepts the correct key', function () {
    $this->getJson('/api/v1/settings', ['X-Internal-Key' => 'test-key'])->assertOk();
});

it('returns json 404 for unknown routes', function () {
    $this->getJson('/api/v1/nope', ['X-Internal-Key' => 'test-key'])->assertNotFound();
});
