<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\BuildsPayloads;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Project */
class ProjectCardResource extends JsonResource
{
    use BuildsPayloads;

    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'summary' => $this->summary,
            'cover' => self::imageOf($this->resource, 'cover', $this->cover_alt),
            'tags' => array_slice($this->stack ?? [], 0, 2),
            // Full list for the technology filter on /projects.
            'stack' => array_values($this->stack ?? []),
            'featured' => $this->featured,
        ];
    }
}
