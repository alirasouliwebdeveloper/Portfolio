<?php

use App\Livewire\GlobalSettings;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());
});

it('shows the site settings button on every admin page', function () {
    $this->get('/admin')->assertOk()->assertSee('Site settings');
});

it('saves brand options from the overlay', function () {
    Setting::factory()->create();

    Livewire::test(GlobalSettings::class)
        ->fillForm([
            'brand_name' => 'Ali Codes',
            'tagline' => 'Full-stack developer',
            'footer_text' => 'All rights reserved.',
            'ga_measurement_id' => 'G-ABC123XYZ9',
            'site_noindex' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Setting::first())
        ->brand_name->toBe('Ali Codes')
        ->ga_measurement_id->toBe('G-ABC123XYZ9')
        ->site_noindex->toBeTrue();
});

it('validates the analytics id format', function () {
    Setting::factory()->create();

    Livewire::test(GlobalSettings::class)
        ->fillForm(['ga_measurement_id' => 'not-an-id'])
        ->call('save')
        ->assertHasFormErrors(['ga_measurement_id']);
});

it('uploads a logo through the overlay', function () {
    $setting = Setting::factory()->create();

    Livewire::test(GlobalSettings::class)
        ->fillForm(['logo' => UploadedFile::fake()->image('logo.png', 400, 120)])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($setting->fresh()->getFirstMedia('logo'))->not->toBeNull();
});
