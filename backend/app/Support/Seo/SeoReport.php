<?php

namespace App\Support\Seo;

final class SeoReport
{
    /** @param  list<SeoCheck>  $checks */
    public function __construct(
        public readonly int $score,
        public readonly array $checks,
    ) {}

    /** 'bad' below 50, 'warn' 50–79, 'good' from 80 — same scale as each check. */
    public function level(): string
    {
        return match (true) {
            $this->score >= 80 => SeoCheck::GOOD,
            $this->score >= 50 => SeoCheck::WARN,
            default => SeoCheck::BAD,
        };
    }

    /** @return list<SeoCheck> */
    public function failing(): array
    {
        return array_values(array_filter($this->checks, fn (SeoCheck $c) => $c->status !== SeoCheck::GOOD));
    }
}
