<?php

namespace App\Support;

/**
 * Read-only helpers over a TipTap/ProseMirror JSON document (the array the
 * Filament RichEditor stores with `->json()`), plus the one write we allow:
 * demoting any H1 so a page's <h1> can only ever come from its `title`.
 */
final class TipTapDocument
{
    /** Node types that end a text block — used to keep words from fusing together. */
    private const BLOCK_TYPES = [
        'paragraph', 'heading', 'blockquote', 'listItem', 'codeBlock', 'tableCell', 'tableHeader',
    ];

    /**
     * A document of plain paragraphs — the shape old list-of-strings bodies
     * are converted to. Blank paragraphs are dropped; nothing left = null.
     *
     * @param  list<string>|string|null  $paragraphs
     * @return array{type: string, content: list<array<string, mixed>>}|null
     */
    public static function fromParagraphs(array|string|null $paragraphs): ?array
    {
        $content = [];

        foreach ((array) $paragraphs as $paragraph) {
            $text = trim((string) $paragraph);

            if ($text !== '') {
                $content[] = ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => $text]]];
            }
        }

        return $content === [] ? null : ['type' => 'doc', 'content' => $content];
    }

    /** @param  array<string, mixed>|null  $doc */
    public static function isEmpty(?array $doc): bool
    {
        return trim(self::text($doc)) === '' && self::images($doc) === [];
    }

    /**
     * @param  array<string, mixed>|null  $doc
     * @return array<string, mixed>|null
     */
    public static function normalize(?array $doc): ?array
    {
        if ($doc === null) {
            return null;
        }

        return self::demoteH1($doc);
    }

    /** @param  array<string, mixed>|null  $doc */
    public static function text(?array $doc): string
    {
        if ($doc === null) {
            return '';
        }

        $text = self::collectText($doc);

        return trim((string) preg_replace("/[ \t]*\n[ \t\n]*/u", "\n", $text));
    }

    /**
     * @param  array<string, mixed>|null  $doc
     * @return list<array{level: int, text: string}>
     */
    public static function headings(?array $doc, ?int $level = null): array
    {
        $found = [];

        self::walk($doc, function (array $node) use (&$found, $level) {
            if (($node['type'] ?? null) !== 'heading') {
                return;
            }

            $nodeLevel = (int) ($node['attrs']['level'] ?? 1);

            if ($level === null || $nodeLevel === $level) {
                $found[] = ['level' => $nodeLevel, 'text' => trim(self::collectText($node))];
            }
        });

        return $found;
    }

    /**
     * @param  array<string, mixed>|null  $doc
     * @return list<array{src: string, alt: string}>
     */
    public static function images(?array $doc): array
    {
        $found = [];

        self::walk($doc, function (array $node) use (&$found) {
            if (($node['type'] ?? null) === 'image') {
                $found[] = [
                    'src' => (string) ($node['attrs']['src'] ?? $node['attrs']['id'] ?? ''),
                    'alt' => trim((string) ($node['attrs']['alt'] ?? '')),
                ];
            }
        });

        return $found;
    }

    /**
     * @param  array<string, mixed>|null  $doc
     * @return list<string> the href of every link mark
     */
    public static function links(?array $doc): array
    {
        $found = [];

        self::walk($doc, function (array $node) use (&$found) {
            foreach ($node['marks'] ?? [] as $mark) {
                if (($mark['type'] ?? null) === 'link' && filled($mark['attrs']['href'] ?? null)) {
                    $found[] = (string) $mark['attrs']['href'];
                }
            }
        });

        return $found;
    }

    /** @param  array<string, mixed>|null  $node */
    private static function walk(?array $node, callable $visit): void
    {
        if ($node === null) {
            return;
        }

        $visit($node);

        foreach ($node['content'] ?? [] as $child) {
            if (is_array($child)) {
                self::walk($child, $visit);
            }
        }
    }

    /** @param  array<string, mixed>  $node */
    private static function collectText(array $node): string
    {
        $type = $node['type'] ?? null;

        if ($type === 'text') {
            return (string) ($node['text'] ?? '');
        }

        if ($type === 'hardBreak') {
            return "\n";
        }

        $text = '';

        foreach ($node['content'] ?? [] as $child) {
            if (is_array($child)) {
                $text .= self::collectText($child);
            }
        }

        return in_array($type, self::BLOCK_TYPES, true) ? $text."\n" : $text;
    }

    /**
     * @param  array<string, mixed>  $node
     * @return array<string, mixed>
     */
    private static function demoteH1(array $node): array
    {
        if (($node['type'] ?? null) === 'heading' && (int) ($node['attrs']['level'] ?? 0) === 1) {
            $node['attrs']['level'] = 2;
        }

        if (isset($node['content']) && is_array($node['content'])) {
            $node['content'] = array_map(
                fn ($child) => is_array($child) ? self::demoteH1($child) : $child,
                $node['content'],
            );
        }

        return $node;
    }
}
