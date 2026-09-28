<?php

namespace App\Support\TipTap;

use Tiptap\Core\Mark;
use Tiptap\Utils\HTML;

/**
 * Inline font size (`<span style="font-size: 1.5em">`). Mirrors the JS mark of
 * the same name in resources/js/filament/rich-content-plugins/site-wp.js.
 */
class FontSize extends Mark
{
    public static $name = 'fontSize';

    public function addAttributes()
    {
        return [
            'size' => [
                'default' => null,
                'parseHTML' => fn ($DOMNode) => $DOMNode->style->fontSize ?? null,
                'renderHTML' => fn ($attributes) => filled($attributes->size ?? null)
                    ? ['style' => 'font-size: '.$attributes->size]
                    : null,
            ],
        ];
    }

    public function parseHTML()
    {
        return [['tag' => 'span[style*=font-size]']];
    }

    public function renderHTML($mark, $HTMLAttributes = [])
    {
        return ['span', HTML::mergeAttributes([], $HTMLAttributes), 0];
    }
}
