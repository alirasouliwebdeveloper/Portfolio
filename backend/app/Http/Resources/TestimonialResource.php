<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\BuildsPayloads;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Testimonial */
class TestimonialResource extends JsonResource
{
    use BuildsPayloads;

    public function toArray(Request $request): array
    {
        return [
            'quote' => $this->quote,
            'name' => $this->name,
            'role' => $this->role,
            'company' => $this->company,
            'initials' => $this->initials,
            'rating' => $this->rating,
            'avatar' => self::imageOf($this->resource, 'avatar', $this->name),
        ];
    }
}
