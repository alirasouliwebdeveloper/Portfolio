<?php

namespace App\DTOs;

/** One page's Google Search performance over a window of days. */
final class SearchPerformance
{
    /**
     * @param  array{query: string, position: float, clicks: int, impressions: int}|null  $keyword  the best-matching query for the focus keyword
     */
    public function __construct(
        public readonly int $clicks,
        public readonly int $impressions,
        public readonly float $ctr,
        public readonly ?float $position,
        public readonly ?array $keyword,
        public readonly int $days,
    ) {}

    /**
     * @param  list<array{keys: list<string>, clicks: float|int, impressions: float|int, position: float|int}>  $rows  Search Analytics rows, one per query
     */
    public static function fromRows(array $rows, ?string $focusKeyword, int $days): self
    {
        $clicks = (int) array_sum(array_column($rows, 'clicks'));
        $impressions = (int) array_sum(array_column($rows, 'impressions'));

        // Average position is impression-weighted, like Search Console's own.
        $weighted = array_sum(array_map(fn (array $row) => $row['position'] * $row['impressions'], $rows));

        return new self(
            clicks: $clicks,
            impressions: $impressions,
            ctr: $impressions > 0 ? $clicks / $impressions : 0.0,
            position: $impressions > 0 ? round($weighted / $impressions, 1) : null,
            keyword: self::bestMatch($rows, $focusKeyword),
            days: $days,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /** @param  array<string, mixed>  $data */
    public static function fromArray(array $data): self
    {
        return new self($data['clicks'], $data['impressions'], $data['ctr'], $data['position'], $data['keyword'], $data['days']);
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array{query: string, position: float, clicks: int, impressions: int}|null
     */
    private static function bestMatch(array $rows, ?string $focusKeyword): ?array
    {
        $needle = mb_strtolower(trim((string) $focusKeyword));

        if ($needle === '') {
            return null;
        }

        $matches = array_filter($rows, fn (array $row) => str_contains(mb_strtolower($row['keys'][0] ?? ''), $needle));

        if ($matches === []) {
            return null;
        }

        usort($matches, fn (array $a, array $b) => $b['impressions'] <=> $a['impressions']);
        $best = $matches[0];

        return [
            'query' => (string) $best['keys'][0],
            'position' => round((float) $best['position'], 1),
            'clicks' => (int) $best['clicks'],
            'impressions' => (int) $best['impressions'],
        ];
    }
}
