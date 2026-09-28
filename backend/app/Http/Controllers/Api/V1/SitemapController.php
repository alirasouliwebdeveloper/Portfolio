<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Support\ContentCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SitemapController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json(ContentCache::remember(['sitemap'], ContentCache::key($request), function () {
            $lastmod = fn ($model) => $model->updated_at?->toIso8601String();

            return ['data' => [
                'posts_per_page' => (int) config('portfolio.posts_per_page'),
                'pages' => Page::query()->where('noindex', false)->get()
                    ->filter(fn (Page $p) => $p->path() !== null)->map(fn (Page $p) => ['path' => $p->path(), 'lastmod' => $lastmod($p)])->values(),
                'posts' => Post::visible()->where('noindex', false)->orderByDesc('published_at')->get(['slug', 'updated_at'])
                    ->map(fn (Post $p) => ['slug' => $p->slug, 'lastmod' => $lastmod($p)])->values(),
                'categories' => Category::published()->where('noindex', false)
                    ->withCount(['posts as published_posts_count' => fn (Builder $q) => $q->visible()])->orderBy('sort_order')->get()
                    ->map(fn (Category $c) => ['slug' => $c->slug, 'lastmod' => $lastmod($c), 'posts_count' => (int) $c->published_posts_count])->values(),
                'projects' => Project::published()->where('noindex', false)->orderBy('sort_order')->get(['slug', 'updated_at'])
                    ->map(fn (Project $p) => ['slug' => $p->slug, 'lastmod' => $lastmod($p)])->values(),
                'services' => Service::published()->where('noindex', false)->orderBy('sort_order')->get(['slug', 'updated_at'])
                    ->map(fn (Service $s) => ['slug' => $s->slug, 'lastmod' => $lastmod($s)])->values(),
            ]];
        }));
    }
}
