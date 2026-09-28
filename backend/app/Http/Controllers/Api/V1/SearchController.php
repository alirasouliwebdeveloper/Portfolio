<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Concerns\BuildsPayloads;
use App\Http\Resources\PostCardResource;
use App\Models\Category;
use App\Models\Post;
use App\Support\ContentCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    use BuildsPayloads;

    public function index(Request $request): JsonResponse
    {
        $params = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'in:relevance,newest'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $term = trim($params['q']);

        return response()->json(ContentCache::remember(['search'], ContentCache::key($request), function () use ($request, $params, $term) {
            $like = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term).'%';
            $matching = fn (): Builder => Post::visible()->where(fn (Builder $q) => $q
                ->whereRaw("title like ? escape '!'", [$like])
                ->orWhereRaw("excerpt like ? escape '!'", [$like])
                ->orWhereRaw("search_text like ? escape '!'", [$like]));

            $categories = $matching()->reorder()
                ->selectRaw('category_id, count(*) as total')->groupBy('category_id')->pluck('total', 'category_id');
            $chips = Category::published()->whereIn('id', $categories->keys())->orderBy('sort_order')->get()
                ->map(fn (Category $c) => ['name' => $c->name, 'slug' => $c->slug, 'count' => (int) $categories[$c->id]])->values();

            $query = $matching()->with(['category', 'media'])
                ->when($params['category'] ?? null, fn (Builder $q, $slug) => $q->whereHas('category', fn (Builder $c) => $c->where('slug', $slug)));

            if (($params['sort'] ?? 'relevance') === 'relevance') {
                $query->orderByRaw("case when title like ? escape '!' then 0 when excerpt like ? escape '!' then 1 else 2 end", [$like, $like]);
            }
            $paginator = $query->orderByDesc('published_at')->orderByDesc('id')->paginate((int) config('portfolio.posts_per_page'));

            return [
                'data' => PostCardResource::collection($paginator->getCollection())->resolve($request),
                'meta' => [...self::meta($paginator), 'query' => $term, 'total_all' => (int) $categories->sum()],
                'categories' => $chips,
            ];
        }));
    }
}
