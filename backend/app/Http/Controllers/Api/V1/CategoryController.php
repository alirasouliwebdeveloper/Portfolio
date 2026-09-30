<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\Concerns\BuildsPayloads;
use App\Models\Category;
use App\Support\ContentCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use BuildsPayloads;

    public function index(Request $request): JsonResponse
    {
        return response()->json(ContentCache::remember(['categories'], ContentCache::key($request), fn () => [
            'data' => CategoryResource::collection($this->query(self::locale($request))->orderBy('sort_order')->get())->resolve($request),
        ]));
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        return response()->json(ContentCache::remember(['categories', "category:{$slug}"], ContentCache::key($request), fn () => [
            'data' => (new CategoryResource($this->query(self::locale($request))->where('slug', $slug)->firstOrFail()))->resolve($request),
        ]));
    }

    private function query(string $locale): Builder
    {
        return Category::published()->locale($locale)->with('media')->withCount(['posts as published_posts_count' => fn (Builder $q) => $q->published()->locale($locale)]);
    }
}
