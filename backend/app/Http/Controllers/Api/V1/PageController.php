<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Concerns\BuildsPayloads;
use App\Http\Resources\PageResource;
use App\Models\Page;
use App\Support\ContentCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    use BuildsPayloads;

    public function show(Request $request, string $key): JsonResponse
    {
        abort_unless(in_array($key, Page::KEYS, true), 404);
        $locale = self::locale($request);

        return response()->json(ContentCache::remember(['pages', ...($key === 'about' ? ['about'] : [])], ContentCache::key($request), function () use ($request, $key, $locale) {
            // A page not yet translated falls back to English rather than 404ing the whole route.
            $page = Page::query()->where('key', $key)->where('locale', $locale)->first()
                ?? Page::forKey($key);

            return ['data' => (new PageResource($page))->resolve($request)];
        }));
    }
}
