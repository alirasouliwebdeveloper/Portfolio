<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\BuildsPayloads;
use App\Models\Page;
use App\Support\RichHtml;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Page */
class PageResource extends JsonResource
{
    use BuildsPayloads;

    public function toArray(Request $request): array
    {
        $body = RichHtml::render($this->body);

        return [
            'key' => $this->key,
            'title' => $this->title,
            'content' => self::htmlify($this->content ?? []),
            'body_html' => $body['html'] ?: null,
            'toc' => $body['toc'],
            'updated_at' => $this->updated_at?->toIso8601String(),
            'seo' => self::seo($this->resource, null),
        ];
    }

    /** Rich-text documents inside the structured content become sanitized HTML strings. */
    private static function htmlify(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (($value['type'] ?? null) === 'doc') {
            return RichHtml::html($value);
        }

        return array_map(fn ($item) => self::htmlify($item), $value);
    }
}
