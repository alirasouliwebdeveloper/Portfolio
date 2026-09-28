<?php

use App\Support\Seo\SeoAnalyzer;
use App\Support\Seo\SeoCheck;
use App\Support\Seo\SeoInput;
use App\Support\TipTapDocument;

function analyze(array $overrides = []): array
{
    $paragraph = fn (string $text) => ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => $text]]];
    $words = implode(' ', array_fill(0, 320, 'filler'));

    $doc = ['type' => 'doc', 'content' => [
        $paragraph('Laravel development in Muscat for growing businesses. '.$words),
        ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Why Laravel development works']]],
        ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Read more', 'marks' => [['type' => 'link', 'attrs' => ['href' => '/services']]]]]],
        ['type' => 'image', 'attrs' => ['src' => '/x.webp', 'alt' => 'Laravel development team at work']],
    ]];

    $input = SeoInput::fromPage(...[
        'title' => 'Laravel Development in Muscat',
        'metaTitle' => 'Laravel Development in Oman | Ali — Full-Stack Developer',
        'metaDescription' => 'Custom Laravel development in Oman: clean architecture, Filament admin panels and fast APIs for businesses in Muscat. Get a free quote today.',
        'focusKeyword' => 'Laravel development',
        'slug' => 'laravel-development',
        'body' => $doc,
        ...$overrides,
    ]);

    $report = app(SeoAnalyzer::class)->analyze($input);

    return [$report, collect($report->checks)->keyBy('id')];
}

it('gives a well optimised page a high score', function () {
    [$report] = analyze();

    expect($report->score)->toBeGreaterThanOrEqual(80)
        ->and($report->level())->toBe(SeoCheck::GOOD);
});

it('scores a page without a focus keyword low and flags it', function () {
    [$report, $checks] = analyze(['focusKeyword' => '']);

    expect($report->score)->toBeLessThan(80)
        ->and($checks['keyword_set']->status)->toBe(SeoCheck::BAD)
        ->and($checks['keyword_set']->field)->toBe('focus_keyword');
});

it('checks the keyword in the title, meta title, description, slug and first 100 words', function () {
    [, $checks] = analyze();

    foreach (['keyword_in_title', 'keyword_in_meta_title', 'keyword_in_meta_description', 'keyword_in_slug', 'keyword_in_intro', 'keyword_in_h2'] as $id) {
        expect($checks[$id]->status)->toBe(SeoCheck::GOOD, $id);
    }
});

it('fails the keyword checks when the keyword is missing', function () {
    [, $checks] = analyze(['metaTitle' => 'Something else entirely', 'metaDescription' => 'Nothing relevant here at all, but long enough to avoid length warnings in this test case for sure.', 'slug' => 'unrelated']);

    expect($checks['keyword_in_meta_title']->status)->toBe(SeoCheck::BAD)
        ->and($checks['keyword_in_meta_description']->status)->toBe(SeoCheck::BAD)
        ->and($checks['keyword_in_slug']->status)->toBe(SeoCheck::BAD);
});

it('judges meta title and description length', function () {
    [, $short] = analyze(['metaTitle' => 'Laravel', 'metaDescription' => 'Too short.']);
    [, $good] = analyze();

    expect($short['meta_title_length']->status)->not->toBe(SeoCheck::GOOD)
        ->and($short['meta_description_length']->status)->toBe(SeoCheck::BAD)
        ->and($good['meta_description_length']->status)->toBe(SeoCheck::GOOD);
});

it('flags keyword stuffing', function () {
    $stuffed = TipTapDocument::fromParagraphs([implode(' ', array_fill(0, 50, 'Laravel development'))]);
    [, $checks] = analyze(['body' => $stuffed]);

    expect($checks['keyword_density']->status)->toBe(SeoCheck::BAD);
});

it('requires alt text on every image', function () {
    $doc = ['type' => 'doc', 'content' => [['type' => 'image', 'attrs' => ['src' => '/x.webp', 'alt' => '']]]];
    [, $checks] = analyze(['body' => $doc]);

    expect($checks['images']->status)->toBe(SeoCheck::BAD);
});

it('flags a body that contains its own H1', function () {
    $doc = ['type' => 'doc', 'content' => [['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [['type' => 'text', 'text' => 'Second title']]]]];
    [, $checks] = analyze(['body' => $doc]);

    expect($checks['single_h1']->status)->toBe(SeoCheck::WARN);
});

it('asks for an internal link', function () {
    $doc = TipTapDocument::fromParagraphs(['Laravel development text without any link.']);
    [, $checks] = analyze(['body' => $doc]);

    expect($checks['internal_link']->status)->toBe(SeoCheck::BAD);
});

it('leaves out structure checks for plain-text records', function () {
    $input = SeoInput::fromPlainText('Title', '', '', 'keyword', 'slug', ['Some plain text about the keyword.']);
    $ids = collect(app(SeoAnalyzer::class)->analyze($input)->checks)->pluck('id');

    expect($ids)->not->toContain('images')->not->toContain('internal_link')->not->toContain('keyword_in_h2');
});

it('compares keywords ignoring case and punctuation', function () {
    [, $checks] = analyze(['title' => 'LARAVEL-development, in Muscat']);

    expect($checks['keyword_in_title']->status)->toBe(SeoCheck::GOOD);
});
