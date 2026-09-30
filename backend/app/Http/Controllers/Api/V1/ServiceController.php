<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Concerns\BuildsPayloads;
use App\Http\Resources\ServiceResource;
use App\Models\Post;
use App\Models\Service;
use App\Support\ContentCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    use BuildsPayloads;

    public function index(Request $request): JsonResponse
    {
        $locale = self::locale($request);

        return response()->json(ContentCache::remember(['services'], ContentCache::key($request), fn () => [
            'data' => Service::published()->locale($locale)->orderBy('sort_order')->get()
                ->map(fn (Service $service) => (new ServiceResource($service, detailed: false))->resolve($request))->values(),
        ]));
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        return response()->json(ContentCache::remember(['services', "service:{$slug}"], ContentCache::key($request), function () use ($request, $slug) {
            $service = Service::published()->locale(self::locale($request))->where('slug', $slug)->with(['media', 'relatedProject.media', 'relatedProject.testimonial.media', 'relatedCategory'])->firstOrFail();

            // Manually chosen posts win; otherwise the newest posts of the related category.
            $manual = $service->posts()->visible()->locale($service->locale)->with(['category', 'media'])->limit(3)->get();
            $posts = $manual->isNotEmpty() ? $manual : Post::visible()->locale($service->locale)->with(['category', 'media'])
                ->when($service->related_category_id, fn ($q) => $q->where('category_id', $service->related_category_id))
                ->orderByDesc('published_at')->limit(3)->get();

            return ['data' => (new ServiceResource($service, $posts))->resolve($request)];
        }));
    }
}
