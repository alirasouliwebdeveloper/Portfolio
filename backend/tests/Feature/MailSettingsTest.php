<?php

use App\Models\Setting;
use App\Support\MailSettings;
use Illuminate\Support\Facades\DB;

it('stores the SMTP password encrypted and never serializes it', function () {
    $setting = Setting::factory()->create(['mail_password' => 's3cret-pass']);

    expect(DB::table('settings')->value('mail_password'))->not->toContain('s3cret-pass')
        ->and($setting->fresh()->mail_password)->toBe('s3cret-pass')
        ->and($setting->fresh()->toArray())->not->toHaveKey('mail_password');
});

it('uses the SMTP settings from the admin instead of .env', function () {
    config(['mail.default' => 'array']);
    Setting::factory()->create([
        'mail_host' => 'mail.alirasouli.ir', 'mail_port' => 465, 'mail_encryption' => 'ssl',
        'mail_username' => 'site@alirasouli.ir', 'mail_password' => 'pw',
        'mail_from_address' => 'site@alirasouli.ir', 'mail_from_name' => 'Ali Rasouli',
    ]);

    MailSettings::apply();

    expect(config('mail.default'))->toBe('smtp')
        ->and(config('mail.mailers.smtp'))->toMatchArray([
            'host' => 'mail.alirasouli.ir', 'port' => 465, 'scheme' => 'smtps', 'username' => 'site@alirasouli.ir', 'password' => 'pw',
        ])
        ->and(config('mail.from'))->toBe(['address' => 'site@alirasouli.ir', 'name' => 'Ali Rasouli']);
});

it('keeps the .env mail settings while no SMTP host is set in the admin', function () {
    config(['mail.default' => 'array']);
    Setting::factory()->create(['mail_host' => null]);

    MailSettings::apply();

    expect(config('mail.default'))->toBe('array');
});
