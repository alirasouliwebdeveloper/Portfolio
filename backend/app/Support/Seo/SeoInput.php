<?php

namespace App\Support\Seo;

use App\Support\TipTapDocument;

/** Everything the analyzer needs, as plain values — one locale of one page. */
final class SeoInput
{
    /**
     * @param  list<array{level: int, text: string}>  $headings  every heading in the body
     * @param  list<array{src: string, alt: string}>  $images
     * @param  list<string>  $links  href of every link in the body
     */
    public function __construct(
        public readonly string $title = '',
        public readonly string $metaTitle = '',
        public readonly string $metaDescription = '',
        public readonly string $focusKeyword = '',
        public readonly string $slug = '',
        public readonly string $bodyText = '',
        public readonly array $headings = [],
        public readonly array $images = [],
        public readonly array $links = [],
        /**
         * False for plain-text records (no headings/images/links to inspect):
         * the structure checks are left out rather than failed forever.
         */
        public readonly bool $structured = true,
    ) {}

    /**
     * @param  array<string, mixed>|null  $body  TipTap JSON document
     * @param  string|null  $lead  visible intro shown above the body (excerpt/description); counts as the start of the text
     */
    public static function fromPage(
        ?string $title,
        ?string $metaTitle,
        ?string $metaDescription,
        ?string $focusKeyword,
        ?string $slug,
        ?array $body,
        ?string $lead = null,
    ): self {
        return new self(
            title: trim((string) $title),
            metaTitle: trim((string) $metaTitle),
            metaDescription: trim((string) $metaDescription),
            focusKeyword: trim((string) $focusKeyword),
            slug: trim((string) $slug),
            bodyText: trim(trim((string) $lead)."\n".TipTapDocument::text($body)),
            headings: TipTapDocument::headings($body),
            images: TipTapDocument::images($body),
            links: TipTapDocument::links($body),
        );
    }

    /**
     * For records that have no rich body (services, categories): plain
     * paragraphs stand in for the body and there are no headings/images/links.
     *
     * @param  list<string>  $paragraphs
     */
    public static function fromPlainText(
        ?string $title,
        ?string $metaTitle,
        ?string $metaDescription,
        ?string $focusKeyword,
        ?string $slug,
        array $paragraphs,
    ): self {
        return new self(
            title: trim((string) $title),
            metaTitle: trim((string) $metaTitle),
            metaDescription: trim((string) $metaDescription),
            focusKeyword: trim((string) $focusKeyword),
            slug: trim((string) $slug),
            bodyText: trim(implode("\n", array_map('strval', $paragraphs))),
            structured: false,
        );
    }
}
