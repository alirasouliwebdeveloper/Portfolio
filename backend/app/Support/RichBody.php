<?php

namespace App\Support;

use App\Filament\Support\WordPressEditorPlugin;
use Filament\Forms\Components\RichEditor\RichContentRenderer;

/**
 * Reads a rich-body value however it happens to be stored. Long texts used to
 * be lists of plain paragraph strings and are TipTap documents now; every
 * reader goes through here so both shapes work — before, during and after the
 * data migration, and for seeders/tests that still write the old shape.
 */
final class RichBody
{
    /**
     * @return array<string, mixed>|null a TipTap document, or null when there is no text
     */
    public static function normalize(mixed $value): ?array
    {
        if (is_array($value) && ($value['type'] ?? null) === 'doc') {
            return $value;
        }

        if (is_string($value) || (is_array($value) && array_is_list($value))) {
            return TipTapDocument::fromParagraphs($value);
        }

        return null;
    }

    /** Sanitized HTML for the public site ('' when empty). */
    public static function html(mixed $value): string
    {
        $doc = self::normalize($value);

        if ($doc === null || TipTapDocument::isEmpty($doc)) {
            return '';
        }

        return RichContentRenderer::make($doc)
            ->plugins([WordPressEditorPlugin::make()])
            ->toHtml();
    }

    /** Plain text with paragraphs separated by newlines. */
    public static function plain(mixed $value): string
    {
        return TipTapDocument::text(self::normalize($value));
    }

    /**
     * One string per text block — the legacy API shape kept for a release so
     * an older frontend never sees a different `body`/`description`.
     *
     * @return list<string>
     */
    public static function paragraphs(mixed $value): array
    {
        $text = self::plain($value);

        return $text === '' ? [] : array_values(array_filter(explode("\n", $text), fn (string $line) => trim($line) !== ''));
    }
}
