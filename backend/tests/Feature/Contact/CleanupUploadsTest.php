<?php

use App\Models\ContactMessage;
use App\Models\Upload;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Storage;

it('deletes expired unattached uploads and their files, and keeps the rest', function () {
    Storage::fake('uploads');
    Storage::disk('uploads')->put('old.pdf', 'x');
    Storage::disk('uploads')->put('fresh.pdf', 'x');
    Storage::disk('uploads')->put('kept.pdf', 'x');

    $old = Upload::factory()->create(['path' => 'old.pdf', 'expires_at' => now()->subHour()]);
    $fresh = Upload::factory()->create(['path' => 'fresh.pdf', 'expires_at' => now()->addHour()]);
    $kept = Upload::factory()->create(['path' => 'kept.pdf', 'expires_at' => null, 'contact_message_id' => ContactMessage::factory()->create()->id]);

    $this->artisan('uploads:cleanup')->assertSuccessful();

    expect(Upload::find($old->id))->toBeNull()
        ->and(Upload::find($fresh->id))->not->toBeNull()
        ->and(Upload::find($kept->id))->not->toBeNull();
    Storage::disk('uploads')->assertMissing('old.pdf');
    Storage::disk('uploads')->assertExists('fresh.pdf');
    Storage::disk('uploads')->assertExists('kept.pdf');
});

it('is scheduled hourly', function () {
    $events = collect(app(Schedule::class)->events())->map->command;

    expect($events->contains(fn ($command) => str_contains((string) $command, 'uploads:cleanup')))->toBeTrue();
});

it('lets an admin download an attachment but not a guest', function () {
    Storage::fake('uploads');
    Storage::disk('uploads')->put('brief.pdf', 'pdf-bytes');
    $upload = Upload::factory()->create(['path' => 'brief.pdf', 'original_name' => 'brief.pdf', 'contact_message_id' => ContactMessage::factory()->create()->id]);

    $this->get(route('contact-files.download', $upload))->assertRedirect();

    $this->actingAs(User::factory()->create())
        ->get(route('contact-files.download', $upload))
        ->assertOk()
        ->assertDownload('brief.pdf');
});
