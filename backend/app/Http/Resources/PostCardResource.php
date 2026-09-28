<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\BuildsPayloads;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Post */
class PostCardResource extends JsonResource
{
    use BuildsPayloads;

    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'cover' => self::imageOf($this->resource, 'cover', $this->cover_alt),
            'category' => ['name' => $this->category->name, 'slug' => $this->category->slug],
            'published_at' => $this->published_at?->toIso8601String(),
            'reading_time' => $this->reading_time,
            'featured' => $this->featured,
        ];
    }
}
