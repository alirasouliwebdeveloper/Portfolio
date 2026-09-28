<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\BuildsPayloads;
use App\Models\Project;
use App\Support\RichHtml;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Project */
class ProjectResource extends JsonResource
{
    use BuildsPayloads;

    public function __construct($resource, private readonly ?Project $next = null)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $html = fn ($value) => ($rendered = RichHtml::html($value)) === '' ? null : $rendered;

        return [
            ...(new ProjectCardResource($this->resource))->toArray($request),
            'lead_html' => $html($this->lead),
            'client' => $this->client,
            'role' => $this->role,
            'timeline' => $this->timeline,
            'year' => $this->year,
            'live_url' => $this->live_url,
            'challenge_html' => $html($this->challenge),
            'solution_html' => $html($this->solution),
            'result_html' => $html($this->result),
            'features' => $this->features ?? [],
            'stack' => $this->stack ?? [],
            'metrics' => $this->metrics ?? [],
            'screens' => $this->screens->map(fn ($screen) => [
                'image' => self::imageOf($screen, 'image', $screen->alt),
                'caption' => $screen->caption,
            ])->values(),
            'testimonial' => $this->testimonial ? (new TestimonialResource($this->testimonial))->toArray($request) : null,
            'service' => $this->service?->isPublished()
                ? ['slug' => $this->service->slug, 'nav_label' => $this->service->nav_label, 'title' => $this->service->title]
                : null,
            'next' => $this->next ? (new ProjectCardResource($this->next))->toArray($request) : null,
            'updated_at' => $this->updated_at?->toIso8601String(),
            'seo' => self::seo($this->resource, $this->summary),
        ];
    }
}
