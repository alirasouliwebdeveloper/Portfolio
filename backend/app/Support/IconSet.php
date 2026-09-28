<?php

namespace App\Support;

/**
 * Fixed icon set stored in the API and mapped to SVG components by the frontend
 * (frontend/src/components/ui/icons.tsx). Keep both lists in sync.
 */
final class IconSet
{
    public const NAMES = [
        'alert', 'bell', 'book', 'bulb', 'calendar', 'card', 'cart', 'chat', 'check', 'clock',
        'code', 'db', 'download', 'file', 'flow', 'grid', 'heart', 'home', 'mail', 'menu',
        'minus', 'phone', 'pin', 'plan', 'plus', 'rocket', 'screen', 'search', 'send', 'server',
        'shield', 'smile', 'trend', 'trophy', 'upload', 'user', 'x',
    ];

    /** @return array<string, string> */
    public static function options(): array
    {
        return array_combine(self::NAMES, array_map('ucfirst', self::NAMES));
    }
}
