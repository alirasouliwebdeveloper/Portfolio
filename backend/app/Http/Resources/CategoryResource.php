<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\BuildsPayloads;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Category */
class CategoryResource extends JsonResource
{
    use BuildsPayloads;

    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'posts_count' => (int) ($this->published_posts_count ?? $this->posts()->published()->count()),
            'seo' => self::seo($this->resource, $this->description),
        ];
    }
}
