<?php

use App\Models\Upload;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

// Admin-only download of a contact-form attachment (files live on a private disk).
Route::middleware(['web', Authenticate::class])
    ->get('/admin/contact-files/{upload:uuid}', function (Upload $upload) {
        abort_unless(Storage::disk('uploads')->exists($upload->path), 404);

        return Storage::disk('uploads')->download($upload->path, $upload->original_name);
    })
    ->name('contact-files.download');
