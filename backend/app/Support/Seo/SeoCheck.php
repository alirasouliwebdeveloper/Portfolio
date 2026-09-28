<?php

namespace App\Support\Seo;

final class SeoCheck
{
    public const GOOD = 'good';

    public const WARN = 'warn';

    public const BAD = 'bad';

    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly string $status,
        public readonly int $weight,
        public readonly string $message,
        /** The form field an editor should change to fix this. */
        public readonly ?string $field = null,
        /** Panel section this check is listed under: basic | meta | content | media. */
        public readonly string $group = 'basic',
    ) {}

    /** Share of the weight this check earns: full, half, or nothing. */
    public function earned(): float
    {
        return match ($this->status) {
            self::GOOD => $this->weight,
            self::WARN => $this->weight / 2,
            default => 0,
        };
    }
}
