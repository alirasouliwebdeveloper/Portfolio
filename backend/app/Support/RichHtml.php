<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Str;

/**
 * Turns a stored rich-text value into the HTML the public site renders: sanitized by Filament,
 * then given heading ids (for the table of contents), image sizes (no layout shift) and safe links.
 */
final class RichHtml
{
    /**
     * @return array{html: string, toc: list<array{id: string, text: string, level: int}>}
     */
    public static function render(mixed $value): array
    {
        $html = RichBody::html($value);

        if ($html === '') {
            return ['html' => '', 'toc' => []];
        }

        $document = new DOMDocument;
        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><div id="rich-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new DOMXPath($document);
        $toc = [];
        $used = [];

        foreach ($xpath->query('//h2 | //h3') as $heading) {
            /** @var DOMElement $heading */
            $text = trim($heading->textContent);
            $base = Str::slug($text) ?: 'section';
            $id = $base;
            for ($i = 2; in_array($id, $used, true); $i++) {
                $id = "{$base}-{$i}";
            }
            $used[] = $id;
            $heading->setAttribute('id', $id);

            if ($heading->nodeName === 'h2') {
                $toc[] = ['id' => $id, 'text' => $text, 'level' => 2];
            }
        }

        foreach ($xpath->query('//img') as $image) {
            /** @var DOMElement $image */
            if (! $image->hasAttribute('width') || ! $image->hasAttribute('height')) {
                [$width, $height] = self::dimensions($image->getAttribute('src'));
                if ($width && $height) {
                    $image->setAttribute('width', (string) $width);
                    $image->setAttribute('height', (string) $height);
                }
            }
            $image->setAttribute('loading', 'lazy');
            $image->setAttribute('decoding', 'async');
        }

        foreach ($xpath->query('//a[@target="_blank"]') as $link) {
            /** @var DOMElement $link */
            $rel = collect(explode(' ', $link->getAttribute('rel')))->push('noopener')->filter()->unique()->implode(' ');
            $link->setAttribute('rel', $rel);
        }

        $root = $document->getElementById('rich-root');
        $inner = '';
        foreach ($root->childNodes as $child) {
            $inner .= $document->saveHTML($child);
        }

        return ['html' => $inner, 'toc' => $toc];
    }

    public static function html(mixed $value): string
    {
        return self::render($value)['html'];
    }

    /** @return array{0: int|null, 1: int|null} */
    private static function dimensions(string $src): array
    {
        $path = parse_url($src, PHP_URL_PATH) ?: '';

        if (! str_starts_with($path, '/storage/')) {
            return [null, null];
        }

        $file = storage_path('app/public/'.substr($path, strlen('/storage/')));
        $size = is_file($file) ? @getimagesize($file) : false;

        return $size ? [$size[0], $size[1]] : [null, null];
    }
}
