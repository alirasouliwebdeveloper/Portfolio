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
        ]);

        return response()->json(ContentCache::remember(['posts'], ContentCache::key($request), function () use ($request, $params) {
            $category = $params['category'] ?? null;
            $query = $this->scope($category)->with(['category', 'media'])->orderByDesc('published_at')->orderByDesc('id');

            if ($request->boolean('exclude_featured') && ($featured = $this->featuredPost($category))) {
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
            $post = $this->featuredPost($params['category'] ?? null);

            return ['data' => $post ? (new PostCardResource($post))->resolve($request) : null];
        }));
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        return response()->json(ContentCache::remember(['posts', "post:{$slug}"], ContentCache::key($request), function () use ($request, $slug) {
            $post = Post::visible()->where('slug', $slug)->with(['category', 'tags', 'relatedService', 'media'])->firstOrFail();

            $previous = Post::visible()
                ->where(fn (Builder $q) => $q->where('published_at', '<', $post->published_at)->orWhere(fn (Builder $q) => $q->where('published_at', $post->published_at)->where('id', '<', $post->id)))
                ->orderByDesc('published_at')->orderByDesc('id')->first();
            $next = Post::visible()
                ->where(fn (Builder $q) => $q->where('published_at', '>', $post->published_at)->orWhere(fn (Builder $q) => $q->where('published_at', $post->published_at)->where('id', '>', $post->id)))
                ->orderBy('published_at')->orderBy('id')->first();

            $related = Post::visible()->whereKeyNot($post->getKey())->with(['category', 'media'])
                ->orderByRaw('category_id = ? desc', [$post->category_id])->orderByDesc('published_at')->limit(3)->get();

            return ['data' => (new PostResource($post, compact('previous', 'next', 'related')))->resolve($request)];
        }));
    }

    private function scope(?string $category): Builder
    {
        return Post::visible()->when($category, fn (Builder $q) => $q->whereHas('category', fn (Builder $c) => $c->where('slug', $category)));
    }

    private function featuredPost(?string $category): ?Post
    {
        return $this->scope($category)->where('featured', true)->with(['category', 'media'])->orderByDesc('published_at')->first();
    }
}
