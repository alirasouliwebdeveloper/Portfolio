<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Concerns\BuildsPayloads;
use App\Http\Resources\TestimonialResource;
use App\Models\Testimonial;
use App\Support\ContentCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    use BuildsPayloads;

    public function index(Request $request): JsonResponse
    {
        $request->validate(['featured' => ['nullable', 'boolean']]);

        return response()->json(ContentCache::remember(['testimonials'], ContentCache::key($request), fn () => [
            'data' => TestimonialResource::collection(
                Testimonial::query()->locale(self::locale($request))->with('media')->when($request->boolean('featured'), fn ($q) => $q->where('featured', true))->orderBy('sort_order')->get(),
            )->resolve($request),
        ]));
    }
}
