<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Concerns\BuildsPayloads;
use App\Http\Resources\PostCardResource;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Support\ContentCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    use BuildsPayloads;

    public function index(Request $request): JsonResponse
    {
        $params = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:24'],
            'category' => ['nullable', 'string', 'max:100'],
            'exclude_featured' => ['nullable', 'boolean'],
            'locale' => ['nullable', 'string'],
        ]);

        return response()->json(ContentCache::remember(['posts'], ContentCache::key($request), function () use ($request, $params) {
            $category = $params['category'] ?? null;
            $locale = self::locale($request);
            $query = $this->scope($category, $locale)->with(['category', 'media'])->orderByDesc('published_at')->orderByDesc('id');

            if ($request->boolean('exclude_featured') && ($featured = $this->featuredPost($category, $locale))) {
                $query->whereKeyNot($featured->getKey());
            }

            $paginator = $query->paginate((int) ($params['per_page'] ?? config('portfolio.posts_per_page')));

            return [
                'data' => PostCardResource::collection($paginator->getCollection())->resolve($request),
                'meta' => self::meta($paginator),
            ];
        }));
    }

    public function featured(Request $request): JsonResponse
    {
        $params = $request->validate(['category' => ['nullable', 'string', 'max:100']]);

        return response()->json(ContentCache::remember(['posts'], ContentCache::key($request), function () use ($request, $params) {
            $post = $this->featuredPost($params['category'] ?? null, self::locale($request));

            return ['data' => $post ? (new PostCardResource($post))->resolve($request) : null];
        }));
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        return response()->json(ContentCache::remember(['posts', "post:{$slug}"], ContentCache::key($request), function () use ($request, $slug) {
            $post = Post::visible()->locale(self::locale($request))->where('slug', $slug)->with(['category', 'tags', 'relatedService', 'media'])->firstOrFail();

            $previous = Post::visible()->locale($post->locale)
                ->where(fn (Builder $q) => $q->where('published_at', '<', $post->published_at)->orWhere(fn (Builder $q) => $q->where('published_at', $post->published_at)->where('id', '<', $post->id)))
                ->orderByDesc('published_at')->orderByDesc('id')->first();
            $next = Post::visible()->locale($post->locale)
                ->where(fn (Builder $q) => $q->where('published_at', '>', $post->published_at)->orWhere(fn (Builder $q) => $q->where('published_at', $post->published_at)->where('id', '>', $post->id)))
                ->orderBy('published_at')->orderBy('id')->first();

            $related = Post::visible()->locale($post->locale)->whereKeyNot($post->getKey())->with(['category', 'media'])
                ->orderByRaw('category_id = ? desc', [$post->category_id])->orderByDesc('published_at')->limit(3)->get();

            return ['data' => (new PostResource($post, compact('previous', 'next', 'related')))->resolve($request)];
        }));
    }

    private function scope(?string $category, string $locale = 'en'): Builder
    {
        return Post::visible()->locale($locale)->when($category, fn (Builder $q) => $q->whereHas('category', fn (Builder $c) => $c->where('slug', $category)));
    }

    private function featuredPost(?string $category, string $locale = 'en'): ?Post
    {
        return $this->scope($category, $locale)->where('featured', true)->with(['category', 'media'])->orderByDesc('published_at')->first();
    }
}
