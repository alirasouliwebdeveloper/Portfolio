<?php

use App\Mail\ContactAutoReply;
use App\Mail\ContactReceived;
use App\Models\ContactMessage;
use App\Models\Setting;
use App\Models\Upload;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

beforeEach(function () {
    config(['portfolio.internal_key' => 'test-key', 'portfolio.mail_to' => 'owner@example.com']);
    Setting::factory()->create(['contact_options' => [
        'needs' => ['Website or web app', 'Online store'],
        'budgets' => ['$1,500 – $4,000', 'Not sure yet'],
        'timelines' => ['Flexible'],
    ]]);
    Mail::fake();
});

function contact(array $overrides = [], bool $withKey = true)
{
    $payload = array_merge([
        'name' => 'Sara Ahmadi',
        'email' => 'sara@securio.store',
        'need' => 'Online store',
        'message' => 'We sell smart locks and need a faster store.',
        'visitor_ip' => '203.0.113.7',
        'user_agent' => 'Mozilla/5.0',
    ], $overrides);

    return test()->postJson('/api/v1/contact', $payload, $withKey ? ['X-Internal-Key' => 'test-key'] : []);
}

it('needs the internal key', function () {
    contact(withKey: false)->assertUnauthorized();
});

it('saves a message, hashes the ip and queues both emails', function () {
    contact(['company' => 'Securio', 'budget' => 'Not sure yet', 'service_slug' => 'online-store-development'])->assertCreated();

    $message = ContactMessage::firstOrFail();
    expect($message)->name->toBe('Sara Ahmadi')->service_slug->toBe('online-store-development')
        ->and($message->status->value)->toBe('new')
        ->and($message->ip_hash)->toBe(hash_hmac('sha256', '203.0.113.7', config('app.key')))
        ->and($message->ip_hash)->not->toContain('203.0.113.7');

    Mail::assertQueued(ContactReceived::class, fn ($mail) => $mail->hasTo('owner@example.com') && $mail->hasReplyTo('sara@securio.store'));
    Mail::assertQueued(ContactAutoReply::class, fn ($mail) => $mail->hasTo('sara@securio.store'));
});

it('validates every field on the server', function (array $payload, string $field) {
    contact($payload)->assertUnprocessable()->assertJsonValidationErrors($field);
    expect(ContactMessage::count())->toBe(0);
})->with([
    'missing name' => [['name' => ''], 'name'],
    'long name' => [['name' => str_repeat('a', 101)], 'name'],
    'bad email' => [['email' => 'not-an-email'], 'email'],
    'short message' => [['message' => 'too short'], 'message'],
    'long message' => [['message' => str_repeat('x', 5001)], 'message'],
    'unknown need' => [['need' => 'Something unlisted'], 'need'],
    'unknown budget' => [['budget' => '$1'], 'budget'],
    'bad ip' => [['visitor_ip' => 'nope'], 'visitor_ip'],
]);

it('attaches the uploads of the same session and keeps them from expiring', function () {
    $session = (string) Str::uuid();
    $uploads = Upload::factory()->count(2)->create(['upload_session' => $session]);

    contact(['upload_session' => $session, 'upload_uuids' => $uploads->pluck('uuid')->all()])->assertCreated();

    $message = ContactMessage::firstOrFail();
    expect($message->uploads)->toHaveCount(2)
        ->and($message->uploads->every(fn ($u) => $u->expires_at === null))->toBeTrue();
});

it('rejects uploads from another session, expired or already attached ones', function () {
    $session = (string) Str::uuid();
    $foreign = Upload::factory()->create(['upload_session' => (string) Str::uuid()]);
    $expired = Upload::factory()->create(['upload_session' => $session, 'expires_at' => now()->subHour()]);

    contact(['upload_session' => $session, 'upload_uuids' => [$foreign->uuid]])->assertUnprocessable()->assertJsonValidationErrors('upload_uuids.0');
    contact(['upload_session' => $session, 'upload_uuids' => [$expired->uuid]])->assertUnprocessable();
    contact(['upload_session' => $session, 'upload_uuids' => [(string) Str::uuid()]])->assertUnprocessable();
});

it('accepts at most 5 attachments', function () {
    $session = (string) Str::uuid();
    $uuids = Upload::factory()->count(6)->create(['upload_session' => $session])->pluck('uuid')->all();

    contact(['upload_session' => $session, 'upload_uuids' => $uuids])->assertUnprocessable()->assertJsonValidationErrors('upload_uuids');
});

it('verifies the Turnstile token server-side and rejects failures', function () {
    config(['services.turnstile.secret' => 'secret']);
    Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => false])]);

    contact(['turnstile_token' => 'bad'])->assertUnprocessable()->assertJsonValidationErrors('turnstile');
    contact(['turnstile_token' => null])->assertUnprocessable()->assertJsonValidationErrors('turnstile');
    expect(ContactMessage::count())->toBe(0);
    Mail::assertNothingQueued();
});

it('accepts a valid Turnstile token and forwards the visitor ip', function () {
    config(['services.turnstile.secret' => 'secret']);
    Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => true])]);

    contact(['turnstile_token' => 'good'])->assertCreated();

    Http::assertSent(fn ($request) => $request['secret'] === 'secret' && $request['response'] === 'good' && $request['remoteip'] === '203.0.113.7');
});

it('fails closed in production when no Turnstile secret is configured', function () {
    app()->detectEnvironment(fn () => 'production');
    config(['services.turnstile.secret' => '']);

    contact()->assertUnprocessable()->assertJsonValidationErrors('turnstile');
});

it('rate limits to 5 messages per hour per visitor ip', function () {
    foreach (range(1, 5) as $i) {
        contact()->assertCreated();
    }

    contact()->assertTooManyRequests();
    contact(['visitor_ip' => '198.51.100.9'])->assertCreated();
});

it('does not touch the sender when saving fails validation', function () {
    contact(['message' => 'short'])->assertUnprocessable();

    Mail::assertNothingQueued();
});
