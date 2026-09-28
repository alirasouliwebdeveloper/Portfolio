<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\BuildsPayloads;
use App\Models\Post;
use App\Models\Setting;
use App\Support\RichHtml;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Post */
class PostResource extends JsonResource
{
    use BuildsPayloads;

    /** @param  array{previous: ?Post, next: ?Post, related: iterable<Post>}  $context */
    public function __construct($resource, private readonly array $context = [])
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $body = RichHtml::render($this->body);
        $settings = Setting::current();
        $link = fn (?Post $post) => $post ? ['slug' => $post->slug, 'title' => $post->title] : null;

        return [
            ...(new PostCardResource($this->resource))->toArray($request),
            'body_html' => $body['html'],
            'toc' => $body['toc'],
            'updated_at' => $this->updated_at?->toIso8601String(),
            'tags' => $this->tags->map(fn ($tag) => ['name' => $tag->name, 'slug' => $tag->slug])->values(),
            'related_service' => $this->relatedService?->isPublished()
                ? ['slug' => $this->relatedService->slug, 'nav_label' => $this->relatedService->nav_label, 'lead' => $this->relatedService->lead, 'icon' => $this->relatedService->icon]
                : null,
            'author' => [
                'name' => $settings->name,
                'headline' => $settings->headline,
                'bio' => $settings->bio_short,
                'photo' => self::imageOf($settings, 'portrait', $settings->portrait_alt),
            ],
            'previous' => $link($this->context['previous'] ?? null),
            'next' => $link($this->context['next'] ?? null),
            'related' => PostCardResource::collection($this->context['related'] ?? [])->resolve($request),
            'seo' => self::seo($this->resource, $this->excerpt),
        ];
    }
}
