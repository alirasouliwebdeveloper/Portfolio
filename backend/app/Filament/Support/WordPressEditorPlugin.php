<?php

namespace App\Filament\Support;

use App\Support\TipTap\FontSize;
use App\Support\TipTap\SiteAttributes;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Support\Enums\Width;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Icons\Heroicon;

/**
 * The parts of a WordPress-style editor that Filament's RichEditor lacks:
 * image settings (alt, title, size, alignment), link settings (nofollow,
 * sponsored, title), font size, block text direction and list indentation.
 * The same instance must be given to RichContentRenderer, otherwise the
 * public site would drop these marks/attributes (see App\Support\RichBody).
 */
class WordPressEditorPlugin implements RichContentPlugin
{
    public const FONT_SIZES = [
        '0.8em' => 'Small',
        '1.25em' => 'Large',
        '1.5em' => 'Extra large',
        '2em' => 'Huge',
        '2.5em' => 'Giant',
    ];

    public static function make(): static
    {
        return app(static::class);
    }

    public function getTipTapPhpExtensions(): array
    {
        return [app(FontSize::class), app(SiteAttributes::class)];
    }

    public function getTipTapJsExtensions(): array
    {
        return [FilamentAsset::getScriptSrc('rich-content-plugins/site-wp')];
    }

    public function getEditorTools(): array
    {
        $direction = fn (string $name, string $label, ?string $dir, Heroicon $icon) => RichEditorTool::make($name)
            ->label($label)
            ->icon($icon)
            ->jsHandler(
                '$getEditor()?.chain().focus()'
                .collect(['paragraph', 'heading', 'listItem', 'blockquote'])
                    ->map(fn (string $type) => ".updateAttributes('{$type}', { dir: ".($dir === null ? 'null' : "'{$dir}'").' })')
                    ->implode('')
                .'.run()'
            );

        return [
            RichEditorTool::make('imageSettings')
                ->label('Image settings (alt, title, size, alignment)')
                ->action(arguments: '{ alt: $getEditor().getAttributes(\'image\')?.alt ?? \'\', title: $getEditor().getAttributes(\'image\')?.title ?? \'\', width: $getEditor().getAttributes(\'image\')?.width ?? \'\', align: $getEditor().getAttributes(\'image\')?.class ?? \'\' }')
                ->icon(Heroicon::Photo)
                ->activeKey('image')
                ->disabledWhenNotActive(),
            RichEditorTool::make('linkSettings')
                ->label('Link settings (new tab, nofollow, title)')
                ->action(arguments: '{ href: $getEditor().getAttributes(\'link\')?.href ?? \'\', target: $getEditor().getAttributes(\'link\')?.target ?? \'\', rel: $getEditor().getAttributes(\'link\')?.rel ?? \'\', title: $getEditor().getAttributes(\'link\')?.title ?? \'\' }')
                ->icon(Heroicon::Link),
            RichEditorTool::make('fontSize')
                ->label('Font size')
                ->action()
                ->icon(Heroicon::Bars3BottomLeft),
            $direction('directionRtl', 'Right-to-left', 'rtl', Heroicon::ArrowLongLeft),
            $direction('directionLtr', 'Left-to-right', 'ltr', Heroicon::ArrowLongRight),
            $direction('directionAuto', 'Reset direction', null, Heroicon::ArrowsRightLeft),
            RichEditorTool::make('indent')
                ->label('Indent list item')
                ->icon(Heroicon::ChevronDoubleRight)
                ->jsHandler('$getEditor()?.chain().focus().sinkListItem(\'listItem\').run()'),
            RichEditorTool::make('outdent')
                ->label('Outdent list item')
                ->icon(Heroicon::ChevronDoubleLeft)
                ->jsHandler('$getEditor()?.chain().focus().liftListItem(\'listItem\').run()'),
        ];
    }

    public function getEditorActions(): array
    {
        return [
            $this->imageSettingsAction(),
            $this->linkSettingsAction(),
            $this->fontSizeAction(),
        ];
    }

    private function imageSettingsAction(): Action
    {
        return Action::make('imageSettings')
            ->modalHeading('Image settings')
            ->modalDescription('Alt text is read by search engines and screen readers; the title shows as a tooltip.')
            ->modalWidth(Width::Large)
            ->fillForm(fn (array $arguments): array => [
                'alt' => $arguments['alt'] ?? '',
                'title' => $arguments['title'] ?? '',
                'width' => $arguments['width'] ?? '',
                'align' => $arguments['align'] ?? '',
            ])
            ->schema([
                TextInput::make('alt')->label('Alt text')->required()->maxLength(160),
                TextInput::make('title')->label('Title (tooltip)')->maxLength(160),
                Select::make('width')
                    ->label('Width')
                    ->options(['' => 'Original size', '25%' => '25%', '50%' => '50%', '75%' => '75%', '100%' => '100%'])
                    ->default(''),
                ToggleButtons::make('align')
                    ->label('Alignment')
                    ->options(['' => 'None', 'alignleft' => 'Left', 'aligncenter' => 'Center', 'alignright' => 'Right'])
                    ->default('')
                    ->inline(),
            ])
            ->action(function (array $arguments, array $data, RichEditor $component): void {
                $component->runCommands(
                    [
                        EditorCommand::make('updateAttributes', arguments: ['image', [
                            'alt' => $data['alt'],
                            'title' => filled($data['title'] ?? null) ? $data['title'] : null,
                            'width' => filled($data['width'] ?? null) ? $data['width'] : null,
                            // A percentage width must not fight a fixed pixel height.
                            'height' => null,
                            'class' => filled($data['align'] ?? null) ? $data['align'] : null,
                        ]]),
                    ],
                    editorSelection: $arguments['editorSelection'],
                );
            });
    }

    private function linkSettingsAction(): Action
    {
        return Action::make('linkSettings')
            ->modalHeading('Link settings')
            ->modalWidth(Width::Large)
            ->fillForm(function (array $arguments): array {
                $rel = collect(explode(' ', (string) ($arguments['rel'] ?? '')))->filter()->all();

                return [
                    'href' => $arguments['href'] ?? '',
                    'newTab' => ($arguments['target'] ?? '') === '_blank',
                    'rel' => array_values(array_intersect($rel, ['nofollow', 'sponsored', 'ugc'])),
                    'title' => $arguments['title'] ?? '',
                ];
            })
            ->schema([
                TextInput::make('href')
                    ->label('URL')
                    ->required()
                    ->placeholder('https://… or /en/services')
                    ->rules(['regex:/^(https?:\/\/|mailto:|tel:|\/|#)/i']),
                Toggle::make('newTab')->label('Open in a new tab'),
                CheckboxList::make('rel')
                    ->label('Search engines')
                    ->options(['nofollow' => 'nofollow — do not pass ranking credit', 'sponsored' => 'sponsored — paid/affiliate link', 'ugc' => 'ugc — user-generated content']),
                TextInput::make('title')->label('Title (tooltip)')->maxLength(160),
            ])
            ->action(function (array $arguments, array $data, RichEditor $component): void {
                $rel = collect($data['rel'] ?? []);

                if ($data['newTab'] ?? false) {
                    $rel->push('noopener');
                }

                $component->runCommands(
                    [
                        EditorCommand::make('setLink', arguments: [[
                            'href' => $data['href'],
                            'target' => ($data['newTab'] ?? false) ? '_blank' : null,
                            'rel' => $rel->unique()->implode(' ') ?: null,
                            'title' => filled($data['title'] ?? null) ? $data['title'] : null,
                        ]]),
                    ],
                    editorSelection: $arguments['editorSelection'],
                );
            });
    }

    private function fontSizeAction(): Action
    {
        return Action::make('fontSize')
            ->modalHeading('Font size')
            ->modalWidth(Width::Small)
            ->schema([
                Select::make('size')
                    ->label('Size (relative to the normal text)')
                    ->options(['' => 'Normal', ...self::FONT_SIZES])
                    ->default('')
                    ->required(false),
            ])
            ->action(function (array $arguments, array $data, RichEditor $component): void {
                $command = filled($data['size'] ?? null)
                    ? EditorCommand::make('setFontSize', arguments: [$data['size']])
                    : EditorCommand::make('unsetFontSize');

                $component->runCommands([$command], editorSelection: $arguments['editorSelection']);
            });
    }
}
