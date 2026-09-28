<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\BuildsPayloads;
use App\Models\Post;
use App\Models\Service;
use App\Support\RichHtml;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Service */
class ServiceResource extends JsonResource
{
    use BuildsPayloads;

    /** @param  iterable<Post>  $posts */
    public function __construct($resource, private readonly iterable $posts = [], private readonly bool $detailed = true)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $summary = [
            'slug' => $this->slug,
            'nav_label' => $this->nav_label,
            'title' => $this->title,
            'icon' => $this->icon,
            'lead' => $this->lead,
        ];

        if (! $this->detailed) {
            return $summary;
        }

        $project = $this->relatedProject?->isPublished() ? $this->relatedProject : null;
        $why = $this->why ?? [];

        return [
            ...$summary,
            'h1' => $this->h1,
            'hero_image' => self::imageOf($this->resource, 'hero_image', $this->hero_image_alt),
            'floating_metric' => $this->floating_metric,
            'pains' => $this->pains ?? [],
            'offers' => $this->offers ?? [],
            'why' => [
                'title' => $why['title'] ?? null,
                'text_html' => RichHtml::html($why['text'] ?? null) ?: null,
                'points' => array_values($why['points'] ?? []),
            ],
            'stack' => $this->stack ?? [],
            'tiers' => $this->tiers ?? [],
            'faq' => $this->faq ?? [],
            'related_project' => $project ? [
                ...(new ProjectCardResource($project))->toArray($request),
                'metrics' => $project->metrics ?? [],
                'testimonial' => $project->testimonial ? (new TestimonialResource($project->testimonial))->toArray($request) : null,
            ] : null,
            'related_category' => $this->relatedCategory?->isPublished()
                ? ['name' => $this->relatedCategory->name, 'slug' => $this->relatedCategory->slug]
                : null,
            'posts' => PostCardResource::collection($this->posts)->resolve($request),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'seo' => self::seo($this->resource, $this->lead),
        ];
    }
}
