<?php

use App\Http\Controllers\DeployController;
use App\Models\Upload;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// The API host has no public pages; send anyone landing on it to the admin.
Route::redirect('/', '/admin');

// Admin-only download of a contact-form attachment (files live on a private disk).
Route::middleware(['web', Authenticate::class])
    ->get('/admin/contact-files/{upload:uuid}', function (Upload $upload) {
        abort_unless(Storage::disk('uploads')->exists($upload->path), 404);

        return Storage::disk('uploads')->download($upload->path, $upload->original_name);
    })
    ->name('contact-files.download');

// Manual "deploy now" on cPanel (the cron `deploy:check` is the automated path). Shared secret, CSRF-exempt.
Route::post('/deploy-hook', DeployController::class)->middleware('throttle:10,1');
