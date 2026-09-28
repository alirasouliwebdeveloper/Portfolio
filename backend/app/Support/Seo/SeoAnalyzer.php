<?php

namespace App\Support\Seo;

use Illuminate\Support\Str;

/**
 * On-page SEO scorer for one locale of one page. Pure: plain values in,
 * a 0–100 score plus per-check feedback out — no I/O, so it is cheap enough
 * to run on every keystroke of the live panel and trivial to unit test.
 * Weights sum to 100; a "warn" earns half its weight.
 *
 * The feedback is written for the admin who reads it, in Persian
 * (lang/fa/seo.php) — independent of the language of the page being scored.
 */
class SeoAnalyzer
{
    private const LANG = 'fa';

    /** Weight and panel section of every check. */
    private const CHECKS = [
        'keyword_set' => ['weight' => 8, 'group' => 'basic'],
        'keyword_in_title' => ['weight' => 9, 'group' => 'basic'],
        'keyword_in_meta_title' => ['weight' => 8, 'group' => 'basic'],
        'keyword_near_start' => ['weight' => 5, 'group' => 'basic'],
        'keyword_in_meta_description' => ['weight' => 7, 'group' => 'basic'],
        'keyword_in_slug' => ['weight' => 4, 'group' => 'basic'],
        'keyword_in_intro' => ['weight' => 8, 'group' => 'basic'],
        'meta_title_length' => ['weight' => 6, 'group' => 'meta'],
        'meta_description_length' => ['weight' => 6, 'group' => 'meta'],
        'single_h1' => ['weight' => 3, 'group' => 'meta'],
        'content_length' => ['weight' => 7, 'group' => 'content'],
        'keyword_density' => ['weight' => 6, 'group' => 'content'],
        'keyword_in_h2' => ['weight' => 6, 'group' => 'content'],
        'subheading_rhythm' => ['weight' => 4, 'group' => 'content'],
        'images' => ['weight' => 8, 'group' => 'media'],
        'internal_link' => ['weight' => 5, 'group' => 'media'],
    ];

    public function analyze(SeoInput $input): SeoReport
    {
        $keyword = $this->canonical($input->focusKeyword);
        $bodyWords = $this->words($input->bodyText);
        $wordCount = count($bodyWords);
        $effectiveTitle = $input->metaTitle !== '' ? $input->metaTitle : $input->title;

        $checks = [
            $this->keywordSet($keyword),
            $this->keywordIn('keyword_in_title', $keyword, $input->title, 'title'),
            $this->keywordIn('keyword_in_meta_title', $keyword, $effectiveTitle, 'meta_title'),
            $this->keywordNearStart($keyword, $effectiveTitle),
            $this->keywordIn('keyword_in_meta_description', $keyword, $input->metaDescription, 'meta_description'),
            $this->keywordInSlug($input, $keyword),
            $this->keywordInIntro($keyword, $bodyWords),
            $this->length('meta_title_length', mb_strlen($effectiveTitle), [50, 60], [40, 70], 'meta_title'),
            $this->length('meta_description_length', mb_strlen($input->metaDescription), [120, 160], [90, 190], 'meta_description'),
            $this->singleH1($input),
            $this->contentLength($wordCount),
            $this->density($keyword, $bodyWords),
            ...($input->structured ? [$this->keywordInSubheading($keyword, $input), $this->subheadingRhythm($input, $wordCount)] : []),
            ...($input->structured ? [$this->images($input, $keyword), $this->internalLink($input)] : []),
        ];

        $total = array_sum(array_map(fn (SeoCheck $c) => $c->weight, $checks));
        $earned = array_sum(array_map(fn (SeoCheck $c) => $c->earned(), $checks));

        return new SeoReport((int) round($earned / $total * 100), $checks);
    }

    private function keywordSet(string $keyword): SeoCheck
    {
        return $keyword !== ''
            ? $this->good('keyword_set', $this->t('checks.keyword_set.good'), 'focus_keyword')
            : $this->bad('keyword_set', $this->t('checks.keyword_set.bad'), 'focus_keyword');
    }

    private function keywordIn(string $id, string $keyword, string $text, string $field): SeoCheck
    {
        if ($keyword === '') {
            return $this->bad($id, $this->t('needs_keyword'), 'focus_keyword');
        }

        return str_contains($this->canonical($text), $keyword)
            ? $this->good($id, $this->t("checks.{$id}.good"), $field)
            : $this->bad($id, $this->t("checks.{$id}.bad"), $field);
    }

    private function keywordNearStart(string $keyword, string $effectiveTitle): SeoCheck
    {
        $id = 'keyword_near_start';

        if ($keyword === '') {
            return $this->bad($id, $this->t('needs_keyword'), 'focus_keyword');
        }

        $position = mb_strpos($this->canonical($effectiveTitle), $keyword);

        return match (true) {
            $position === false => $this->bad($id, $this->t('checks.keyword_near_start.missing'), 'meta_title'),
            $position <= 25 => $this->good($id, $this->t('checks.keyword_near_start.good'), 'meta_title'),
            $position <= 45 => $this->warn($id, $this->t('checks.keyword_near_start.warn'), 'meta_title'),
            default => $this->bad($id, $this->t('checks.keyword_near_start.buried'), 'meta_title'),
        };
    }

    /**
     * @param  array{0: int, 1: int}  $ideal
     * @param  array{0: int, 1: int}  $acceptable
     */
    private function length(string $id, int $length, array $ideal, array $acceptable, string $field): SeoCheck
    {
        $replace = [
            'what' => $this->t("what.{$field}"),
            'range' => "{$ideal[0]}–{$ideal[1]}",
            'length' => $length,
        ];

        return match (true) {
            $length === 0 => $this->bad($id, $this->t('length.empty', $replace), $field),
            $length >= $ideal[0] && $length <= $ideal[1] => $this->good($id, $this->t('length.good', $replace), $field),
            $length >= $acceptable[0] && $length <= $acceptable[1] => $this->warn($id, $this->t('length.off', $replace), $field),
            default => $this->bad($id, $this->t('length.off', $replace), $field),
        };
    }

    /** @param  list<string>  $bodyWords */
    private function keywordInIntro(string $keyword, array $bodyWords): SeoCheck
    {
        $id = 'keyword_in_intro';

        if ($keyword === '') {
            return $this->bad($id, $this->t('needs_keyword'), 'focus_keyword');
        }

        return str_contains(implode(' ', array_slice($bodyWords, 0, 100)), $keyword)
            ? $this->good($id, $this->t('checks.keyword_in_intro.good'), 'body')
            : $this->bad($id, $this->t('checks.keyword_in_intro.bad'), 'body');
    }

    private function keywordInSubheading(string $keyword, SeoInput $input): SeoCheck
    {
        $id = 'keyword_in_h2';

        if ($keyword === '') {
            return $this->bad($id, $this->t('needs_keyword'), 'focus_keyword');
        }

        foreach ($input->headings as $heading) {
            if ($heading['level'] === 2 && str_contains($this->canonical($heading['text']), $keyword)) {
                return $this->good($id, $this->t('checks.keyword_in_h2.good'), 'body');
            }
        }

        return $this->bad($id, $this->t('checks.keyword_in_h2.bad'), 'body');
    }

    private function keywordInSlug(SeoInput $input, string $keyword): SeoCheck
    {
        $id = 'keyword_in_slug';

        if ($keyword === '') {
            return $this->bad($id, $this->t('needs_keyword'), 'focus_keyword');
        }

        // The slug is shared by every language and stays Latin, so a keyword
        // written in Arabic script can never match it — don't penalise that.
        if (! preg_match('/[a-z0-9]/i', $keyword)) {
            return $this->good($id, $this->t('checks.keyword_in_slug.skipped'), 'slug');
        }

        return str_contains($input->slug, Str::slug($keyword))
            ? $this->good($id, $this->t('checks.keyword_in_slug.good'), 'slug')
            : $this->bad($id, $this->t('checks.keyword_in_slug.bad'), 'slug');
    }

    /** @param  list<string>  $bodyWords */
    private function density(string $keyword, array $bodyWords): SeoCheck
    {
        $id = 'keyword_density';

        if ($keyword === '') {
            return $this->bad($id, $this->t('needs_keyword'), 'focus_keyword');
        }

        $total = count($bodyWords);

        if ($total === 0) {
            return $this->bad($id, $this->t('checks.keyword_density.no_content'), 'body');
        }

        $occurrences = mb_substr_count(implode(' ', $bodyWords), $keyword);
        $percent = round($occurrences / $total * 100, 1);
        $summary = $this->t('checks.keyword_density.summary', ['count' => $occurrences, 'total' => $total, 'percent' => $percent]);

        $message = fn (string $variant) => $this->t("checks.keyword_density.{$variant}", ['summary' => $summary]);

        return match (true) {
            $percent >= 0.5 && $percent <= 2.5 => $this->good($id, $message('good'), 'body'),
            $percent >= 0.2 && $percent < 0.5 => $this->warn($id, $message('low'), 'body'),
            $percent > 2.5 && $percent <= 3.5 => $this->warn($id, $message('high'), 'body'),
            $percent > 3.5 => $this->bad($id, $message('stuffing'), 'body'),
            default => $this->bad($id, $message('neglected'), 'body'),
        };
    }

    private function contentLength(int $wordCount): SeoCheck
    {
        $id = 'content_length';

        return match (true) {
            $wordCount >= 300 => $this->good($id, $this->t('checks.content_length.good', ['count' => $wordCount]), 'body'),
            $wordCount >= 150 => $this->warn($id, $this->t('checks.content_length.short', ['count' => $wordCount]), 'body'),
            default => $this->bad($id, $this->t('checks.content_length.short', ['count' => $wordCount]), 'body'),
        };
    }

    private function images(SeoInput $input, string $keyword): SeoCheck
    {
        $id = 'images';

        if ($input->images === []) {
            return $this->bad($id, $this->t('checks.images.none'), 'body');
        }

        $withoutAlt = count(array_filter($input->images, fn (array $image) => $image['alt'] === ''));

        if ($withoutAlt > 0) {
            return $this->bad($id, $this->t('checks.images.missing_alt', ['count' => $withoutAlt]), 'body');
        }

        if ($keyword === '') {
            return $this->good($id, $this->t('checks.images.good_plain'), 'body');
        }

        foreach ($input->images as $image) {
            if (str_contains($this->canonical($image['alt']), $keyword)) {
                return $this->good($id, $this->t('checks.images.good'), 'body');
            }
        }

        return $this->warn($id, $this->t('checks.images.no_keyword'), 'body');
    }

    private function internalLink(SeoInput $input): SeoCheck
    {
        $id = 'internal_link';

        if ($input->links === []) {
            return $this->bad($id, $this->t('checks.internal_link.none'), 'body');
        }

        foreach ($input->links as $href) {
            if ((str_starts_with($href, '/') && ! str_starts_with($href, '//')) || $this->isOwnDomain($href)) {
                return $this->good($id, $this->t('checks.internal_link.good'), 'body');
            }
        }

        return $this->warn($id, $this->t('checks.internal_link.external_only'), 'body');
    }

    private function isOwnDomain(string $href): bool
    {
        $host = parse_url((string) config('portfolio.frontend_url'), PHP_URL_HOST);

        return $host !== null && $host !== false && str_contains($href, $host);
    }

    private function subheadingRhythm(SeoInput $input, int $wordCount): SeoCheck
    {
        $id = 'subheading_rhythm';

        $count = count(array_filter($input->headings, fn (array $h) => $h['level'] === 2));
        $expected = max(1, intdiv($wordCount, 300));
        $replace = ['count' => $count, 'words' => $wordCount];

        return match (true) {
            $count === 0 => $this->bad($id, $this->t('checks.subheading_rhythm.none'), 'body'),
            $count >= $expected => $this->good($id, $this->t('checks.subheading_rhythm.good', $replace), 'body'),
            default => $this->warn($id, $this->t('checks.subheading_rhythm.few', $replace), 'body'),
        };
    }

    private function singleH1(SeoInput $input): SeoCheck
    {
        $id = 'single_h1';

        if ($input->title === '') {
            return $this->bad($id, $this->t('checks.single_h1.no_title'), 'title');
        }

        foreach ($input->headings as $heading) {
            if ($heading['level'] === 1) {
                return $this->warn($id, $this->t('checks.single_h1.body_h1'), 'body');
            }
        }

        return $this->good($id, $this->t('checks.single_h1.good'), 'title');
    }

    /**
     * Lower-case, strip Arabic diacritics/tatweel, unify alef/ya/ta-marbuta
     * forms, and collapse everything that is not a letter or digit to single
     * spaces — so "Post-Industrial" and "post industrial" (or أَعَادَة / اعادة)
     * compare equal.
     */
    private function canonical(string $text): string
    {
        return implode(' ', $this->words($text));
    }

    /** @return list<string> */
    private function words(string $text): array
    {
        $text = mb_strtolower($text);
        $text = (string) preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{0640}]/u', '', $text);
        $text = strtr($text, ['أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ٱ' => 'ا', 'ى' => 'ي', 'ة' => 'ه']);

        return preg_split('/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }

    /** @param  array<string, string|int|float>  $replace */
    private function t(string $key, array $replace = []): string
    {
        return trans("seo.{$key}", $replace, self::LANG);
    }

    private function make(string $status, string $id, string $message, ?string $field): SeoCheck
    {
        return new SeoCheck(
            $id,
            $this->t("checks.{$id}.label"),
            $status,
            self::CHECKS[$id]['weight'],
            $message,
            $field,
            self::CHECKS[$id]['group'],
        );
    }

    private function good(string $id, string $message, ?string $field): SeoCheck
    {
        return $this->make(SeoCheck::GOOD, $id, $message, $field);
    }

    private function warn(string $id, string $message, ?string $field): SeoCheck
    {
        return $this->make(SeoCheck::WARN, $id, $message, $field);
    }

    private function bad(string $id, string $message, ?string $field): SeoCheck
    {
        return $this->make(SeoCheck::BAD, $id, $message, $field);
    }
}
