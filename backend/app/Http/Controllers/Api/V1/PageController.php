<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Page;
use App\Support\ContentCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show(Request $request, string $key): JsonResponse
    {
        abort_unless(in_array($key, Page::KEYS, true), 404);

        return response()->json(ContentCache::remember(['pages', ...($key === 'about' ? ['about'] : [])], ContentCache::key($request), fn () => [
            'data' => (new PageResource(Page::forKey($key)))->resolve($request),
        ]));
    }
}
