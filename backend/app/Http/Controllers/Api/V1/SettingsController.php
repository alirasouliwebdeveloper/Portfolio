<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SettingsResource;
use App\Models\Setting;
use App\Support\ContentCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json(ContentCache::remember(['settings'], ContentCache::key($request), fn () => [
            'data' => (new SettingsResource(Setting::current()))->resolve($request),
        ]));
    }
}
