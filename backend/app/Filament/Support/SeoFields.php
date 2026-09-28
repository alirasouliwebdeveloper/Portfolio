<?php

namespace App\Filament\Support;

use App\DTOs\SearchPerformance;
use App\Services\SearchConsoleService;
use App\Support\Seo\SeoAnalyzer;
use App\Support\Seo\SeoInput;
use Closure;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;

/** The three SEO inputs plus the live score panel, shared by every resource that has them. */
class SeoFields
{
    /**
     * @return list<TextInput|Textarea>
     */
    public static function fields(): array
    {
        return [
            TextInput::make('meta_title')
                ->label('SEO title')
                ->maxLength(70)
                ->live(debounce: 800)
                ->helperText('The full browser-tab / Google title (aim for 50–60 characters). The site name is not added automatically. Falls back to the normal title when empty.')
                ->columnSpanFull(),
            Textarea::make('meta_description')
                ->label('SEO meta description')
                ->rows(3)
                ->maxLength(200)
                ->live(debounce: 800)
                ->helperText('Aim for 120–160 characters. Falls back to the summary when empty.')
                ->columnSpanFull(),
            TextInput::make('focus_keyword')
                ->label('Focus keyword')
                ->maxLength(80)
                ->live(debounce: 800)
                ->helperText('The phrase people should find this page for. The SEO score is built around it.')
                ->columnSpanFull(),
        ];
    }

    /** The whole "SEO" tab: meta fields, advanced options (canonical, robots, social image). */
    public static function tab(bool $advanced = true): Tab
    {
        return Tab::make('SEO')
            ->icon('heroicon-o-magnifying-glass')
            ->schema([
                ...self::fields(),
                ...($advanced ? [self::advanced()] : [Fields::image('og_image', 'Social sharing image (Open Graph)')]),
            ]);
    }

    private static function advanced(): Section
    {
        return Section::make('Advanced')
            ->collapsible()
            ->collapsed()
            ->schema([
                TextInput::make('canonical_url')
                    ->label('Canonical URL')
                    ->url()
                    ->maxLength(2048)
                    ->helperText('Only set this when the same content also lives at another URL. Empty = the page URL itself.'),
                Toggle::make('noindex')
                    ->label('Hide from search engines (noindex)')
                    ->helperText('Adds noindex to the page and removes it from the sitemap.'),
                Fields::image('og_image', 'Social sharing image (Open Graph)'),
            ]);
    }

    /**
     * Tabs on the left, sticky live SEO panel on the right — the panel stays visible on every tab.
     *
     * @param  list<Tab>  $tabs
     * @param  Closure(Get): SeoInput  $input
     * @param  (Closure(Get): ?string)|null  $path
     */
    public static function layout(array $tabs, Closure $input, ?Closure $path = null, bool $advanced = true): Grid
    {
        return Grid::make(['default' => 1, 'lg' => 3])
            ->columnSpanFull()
            ->schema([
                Group::make([Tabs::make()->tabs([...$tabs, self::tab($advanced)])->persistTabInQueryString()])
                    ->columnSpan(['lg' => 2]),
                Group::make([self::panel($input, $path)])
                    ->columnSpan(['lg' => 1]),
            ]);
    }

    /**
     * The standard editing layout: the resource's own fields plus the SEO
     * inputs in a 2/3 column, and the sticky live SEO panel in the 1/3 column.
     *
     * @param  list<Component>  $fields
     * @param  Closure(Get): SeoInput  $input
     * @param  (Closure(Get): ?string)|null  $path
     */
    public static function withPanel(array $fields, Closure $input, ?Closure $path = null): Grid
    {
        return Grid::make(['default' => 1, 'lg' => 3])
            ->columnSpanFull()
            ->schema([
                Group::make([...$fields, ...self::fields()])
                    ->columns(2)
                    ->columnSpan(['lg' => 2]),
                Group::make([self::panel($input, $path)])
                    ->columnSpan(['lg' => 1]),
            ]);
    }

    /**
     * Flatten a (simple) Repeater's state — string items, possibly keyed by
     * uuid — into a plain list of non-empty strings.
     *
     * @return list<string>
     */
    public static function paragraphs(mixed $state): array
    {
        return collect(is_array($state) ? $state : [])
            ->map(fn ($item) => is_array($item) ? (string) (reset($item) ?: '') : (string) $item)
            ->filter(fn (string $text) => trim($text) !== '')
            ->values()
            ->all();
    }

    /**
     * Sticky side panel that re-scores the locale being edited as the form
     * changes, plus (when Search Console is configured) how the saved page
     * is actually performing in Google.
     *
     * @param  Closure(Get): SeoInput  $input  builds the analyzer input from live form state
     * @param  (Closure(Get): ?string)|null  $path  the page's path within its locale (e.g. "/my-page"), or null while it has no public URL
     */
    public static function panel(Closure $input, ?Closure $path = null): View
    {
        return View::make('filament.seo-panel')
            ->viewData(function (Get $get, $livewire) use ($input, $path): array {
                $locale = $livewire->activeLocale ?? app()->getLocale();

                return [
                    'report' => app(SeoAnalyzer::class)->analyze($input($get)),
                    'locale' => $locale,
                    'keyword' => trim((string) $get('focus_keyword')),
                    'gsc' => $path ? self::searchConsole($livewire, $locale, $path($get), $get('focus_keyword')) : null,
                ];
            });
    }

    /**
     * @return SearchPerformance|false|null null = feature off / nothing to look up, false = configured but no data
     */
    private static function searchConsole($livewire, string $locale, ?string $path, mixed $keyword): SearchPerformance|false|null
    {
        $service = app(SearchConsoleService::class);

        // Only a saved page can have search traffic.
        if (blank($path) || ! data_get($livewire, 'record.exists', false) || ! $service->isEnabled()) {
            return null;
        }

        $url = $service->pageBaseUrl().$path;

        return $service->performance($url, is_string($keyword) ? $keyword : null) ?? false;
    }
}
