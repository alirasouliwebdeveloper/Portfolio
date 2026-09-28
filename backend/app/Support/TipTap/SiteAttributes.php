<?php

namespace App\Support\TipTap;

use Tiptap\Core\Extension;

/**
 * Attributes the stock editor doesn't keep: text direction on blocks, an
 * alignment class on images, and a title on links. Only *declared* attributes
 * survive in TipTap, so these are declared here (server) and in
 * site-wp.js (browser).
 */
class SiteAttributes extends Extension
{
    public static $name = 'siteAttributes';

    public function addGlobalAttributes()
    {
        return [
            [
                'types' => ['paragraph', 'heading', 'listItem', 'blockquote'],
                'attributes' => [
                    'dir' => [
                        'default' => null,
                        'renderHTML' => fn ($attributes) => in_array($attributes->dir ?? null, ['rtl', 'ltr'], true)
                            ? ['dir' => $attributes->dir]
                            : null,
                    ],
                ],
            ],
            [
                'types' => ['image'],
                'attributes' => [
                    'class' => [
                        'default' => null,
                        'renderHTML' => fn ($attributes) => in_array($attributes->class ?? null, ['alignleft', 'aligncenter', 'alignright'], true)
                            ? ['class' => $attributes->class]
                            : null,
                    ],
                ],
            ],
            [
                'types' => ['link'],
                'attributes' => [
                    'title' => [
                        'default' => null,
                        'renderHTML' => fn ($attributes) => filled($attributes->title ?? null)
                            ? ['title' => $attributes->title]
                            : null,
                    ],
                ],
            ],
        ];
    }
}
