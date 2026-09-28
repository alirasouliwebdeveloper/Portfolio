<?php

use App\Http\Controllers\Api\V1\AboutController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\PageController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Controllers\Api\V1\SettingsController;
use App\Http\Controllers\Api\V1\SitemapController;
use App\Http\Controllers\Api\V1\TestimonialController;
use Illuminate\Support\Facades\Route;

/*
 * Read-only content API for the Next.js server. Every route needs the shared X-Internal-Key.
 * Responses are cached in Redis and cleared by model observers (see App\Support\ContentTags).
 */
Route::prefix('v1')->middleware('internal.key')->group(function () {
    Route::get('settings', [SettingsController::class, 'show']);
    Route::get('pages/{key}', [PageController::class, 'show']);

    Route::get('posts', [PostController::class, 'index']);
    Route::get('posts/featured', [PostController::class, 'featured']);
    Route::get('posts/{slug}', [PostController::class, 'show']);
    Route::get('search', [SearchController::class, 'index']);

    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('categories/{slug}', [CategoryController::class, 'show']);

    Route::get('projects', [ProjectController::class, 'index']);
    Route::get('projects/{slug}', [ProjectController::class, 'show']);
    Route::get('testimonials', [TestimonialController::class, 'index']);

    Route::get('services', [ServiceController::class, 'index']);
    Route::get('services/{slug}', [ServiceController::class, 'show']);

    Route::get('experiences', [AboutController::class, 'experiences']);
    Route::get('process-steps', [AboutController::class, 'processSteps']);
    Route::get('faqs', [AboutController::class, 'faqs']);

    Route::get('sitemap', [SitemapController::class, 'show']);
});
