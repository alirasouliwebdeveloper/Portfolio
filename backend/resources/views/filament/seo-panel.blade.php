@php
    use App\Support\Seo\SeoCheck;

    // The whole panel is read by a Persian-speaking admin: Persian text, right-to-left.
    $t = fn (string $key, array $replace = []) => trans("seo.{$key}", $replace, 'fa');

    $level = $report->level();
    $levelColor = ['good' => '#16a34a', 'warn' => '#f59e0b', 'bad' => '#dc2626'][$level];
    $badgeColor = ['good' => 'success', 'warn' => 'warning', 'bad' => 'danger'][$level];
    $icons = ['good' => 'heroicon-m-check-circle', 'warn' => 'heroicon-m-exclamation-triangle', 'bad' => 'heroicon-m-x-circle'];
    $iconColor = ['good' => '#16a34a', 'warn' => '#f59e0b', 'bad' => '#dc2626'];
    $order = [SeoCheck::BAD => 0, SeoCheck::WARN => 1, SeoCheck::GOOD => 2];

    $counts = ['bad' => 0, 'warn' => 0, 'good' => 0];
    foreach ($report->checks as $check) {
        $counts[$check->status]++;
    }

    $radius = 42;
    $circumference = 2 * M_PI * $radius;
    $dashOffset = $circumference * (1 - $report->score / 100);

    $groups = collect($report->checks)
        ->sortBy(fn ($check) => $order[$check->status])
        ->groupBy('group');

    // Inline styles on purpose: they follow the panel's light/dark text colour, whereas
    // Tailwind colour utilities that Filament itself doesn't use aren't in its compiled CSS.
    $mutedStyle = 'font-size: 0.75rem; line-height: 1.2rem; opacity: 0.65;';
    $strongStyle = 'font-size: 0.875rem; line-height: 1.3rem; font-weight: 600;';
@endphp

@include('filament.partials.persian-font')

<div dir="rtl" class="seo-persian" style="position: sticky; top: 5rem; text-align: right;">
    <x-filament::section compact>
        <x-slot name="heading">{{ $t('panel.heading') }}</x-slot>
        <x-slot name="afterHeader">
            <x-filament::badge :color="$badgeColor" size="lg">{{ $t("panel.levels.{$level}") }}</x-filament::badge>
        </x-slot>

        {{-- Score gauge + counts --}}
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.25rem;">
            <div style="position: relative; width: 96px; height: 96px; flex: none;">
                <svg viewBox="0 0 100 100" width="96" height="96" style="transform: rotate(-90deg);" aria-hidden="true">
                    <circle cx="50" cy="50" r="{{ $radius }}" fill="none" stroke="rgba(128, 128, 128, 0.25)" stroke-width="9" />
                    <circle cx="50" cy="50" r="{{ $radius }}" fill="none" stroke="{{ $levelColor }}" stroke-width="9" stroke-linecap="round"
                        stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $dashOffset }}" />
                </svg>
                <div dir="ltr" style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1.1;">
                    <span style="font-size: 1.6rem; font-weight: 700; color: {{ $levelColor }};">{{ $report->score }}</span>
                    <span style="{{ $mutedStyle }}">/ 100</span>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.4rem;">
                <div style="{{ $strongStyle }}">{{ $t('panel.score') }}</div>
                <div style="display: flex; flex-wrap: wrap; gap: 0.35rem;">
                    <x-filament::badge color="danger">{{ $counts['bad'] }} {{ $t('panel.summary.bad') }}</x-filament::badge>
                    <x-filament::badge color="warning">{{ $counts['warn'] }} {{ $t('panel.summary.warn') }}</x-filament::badge>
                    <x-filament::badge color="success">{{ $counts['good'] }} {{ $t('panel.summary.good') }}</x-filament::badge>
                </div>
                <div style="{{ $mutedStyle }}">{{ $t('panel.language') }}: {{ $t("panel.languages.{$locale}") }}</div>
            </div>
        </div>

        {{-- Checks, grouped like RankMath's accordions --}}
        <div style="display: flex; flex-direction: column; gap: 1.1rem;">
            @foreach (['basic', 'meta', 'content', 'media'] as $groupKey)
                @continue (! $groups->has($groupKey))

                <div>
                    <div style="font-size: 0.8rem; font-weight: 700; opacity: 0.85; margin-bottom: 0.55rem; padding-bottom: 0.3rem; border-bottom: 1px solid rgba(128, 128, 128, 0.2);">{{ $t("groups.{$groupKey}") }}</div>
                    <div style="display: flex; flex-direction: column; gap: 0.7rem;">
                        @foreach ($groups[$groupKey] as $check)
                            <div style="display: flex; gap: 0.5rem; align-items: flex-start;">
                                <x-filament::icon :icon="$icons[$check->status]" style="width: 1.25rem; height: 1.25rem; flex: none; color: {{ $iconColor[$check->status] }};" />
                                <div>
                                    <div style="{{ $strongStyle }}">{{ $check->label }}</div>
                                    <div style="{{ $mutedStyle }}">
                                        {{ $check->message }}
                                        @if ($check->status !== SeoCheck::GOOD && $check->field)
                                            <span style="opacity: 0.75;">— {{ $t('panel.fix_in') }}: {{ $t("fields.{$check->field}") }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Google Search Console (only when configured) --}}
        @if ($gsc !== null)
            <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid rgba(128, 128, 128, 0.25);">
                <div style="{{ $strongStyle }}">{{ $t('panel.gsc.heading') }}</div>

                @if ($gsc === false)
                    <div style="{{ $mutedStyle }}">{{ $t('panel.gsc.unavailable') }}</div>
                @else
                    <div style="{{ $mutedStyle }} margin-bottom: 0.5rem;">{{ $t('panel.gsc.period', ['days' => $gsc->days]) }}</div>
                    <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.6rem;">
                        <div><div style="{{ $mutedStyle }}">{{ $t('panel.gsc.position') }}</div><div dir="ltr" style="{{ $strongStyle }} text-align: right;">{{ $gsc->position !== null ? number_format($gsc->position, 1) : '—' }}</div></div>
                        <div><div style="{{ $mutedStyle }}">{{ $t('panel.gsc.impressions') }}</div><div dir="ltr" style="{{ $strongStyle }} text-align: right;">{{ number_format($gsc->impressions) }}</div></div>
                        <div><div style="{{ $mutedStyle }}">{{ $t('panel.gsc.clicks') }}</div><div dir="ltr" style="{{ $strongStyle }} text-align: right;">{{ number_format($gsc->clicks) }}</div></div>
                        <div><div style="{{ $mutedStyle }}">{{ $t('panel.gsc.ctr') }}</div><div dir="ltr" style="{{ $strongStyle }} text-align: right;">{{ number_format($gsc->ctr * 100, 1) }}%</div></div>
                    </div>

                    @if ($keyword !== '')
                        <div style="{{ $mutedStyle }} margin-top: 0.75rem;">
                            {{-- Values are escaped and wrapped in <bdi> so English words/numbers don't scramble the Persian sentence. --}}
                            @if ($gsc->keyword)
                                {!! $t('panel.gsc.keyword', [
                                    'query' => '<bdi>'.e($gsc->keyword['query']).'</bdi>',
                                    'position' => '<bdi>'.number_format($gsc->keyword['position'], 1).'</bdi>',
                                    'impressions' => '<bdi>'.number_format($gsc->keyword['impressions']).'</bdi>',
                                    'clicks' => '<bdi>'.number_format($gsc->keyword['clicks']).'</bdi>',
                                ]) !!}
                            @else
                                {!! $t('panel.gsc.no_keyword_data', ['keyword' => '<bdi>'.e($keyword).'</bdi>']) !!}
                            @endif
                        </div>
                    @endif
                @endif
            </div>
        @endif
    </x-filament::section>
</div>
