<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Concerns\BuildsPayloads;
use App\Http\Resources\ProjectCardResource;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Support\ContentCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use BuildsPayloads;

    public function index(Request $request): JsonResponse
    {
        $request->validate(['featured' => ['nullable', 'boolean']]);
        $locale = self::locale($request);

        return response()->json(ContentCache::remember(['projects'], ContentCache::key($request), fn () => [
            'data' => ProjectCardResource::collection(
                Project::published()->locale($locale)->with('media')->when($request->boolean('featured'), fn ($q) => $q->where('featured', true))->orderBy('sort_order')->get(),
            )->resolve($request),
        ]));
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        return response()->json(ContentCache::remember(['projects', "project:{$slug}"], ContentCache::key($request), function () use ($request, $slug) {
            $project = Project::published()->locale(self::locale($request))->where('slug', $slug)->with(['media', 'screens.media', 'testimonial.media', 'service'])->firstOrFail();

            $ordered = Project::published()->locale($project->locale)->orderBy('sort_order')->orderBy('id')->pluck('id')->values();
            $position = $ordered->search($project->id);
            $nextId = $ordered->count() > 1 ? $ordered[($position + 1) % $ordered->count()] : null;
            $next = $nextId ? Project::with('media')->find($nextId) : null;

            return ['data' => (new ProjectResource($project, $next))->resolve($request)];
        }));
    }
}
