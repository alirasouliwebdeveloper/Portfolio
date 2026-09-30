<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Filament\Support\Fields;
use App\Filament\Support\SeoFields;
use App\Support\Seo\SeoInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            SeoFields::layout(
                tabs: [
                    Tab::make('Category')->icon('heroicon-o-folder')->schema([
                        TextInput::make('name')->label('Name (H1)')->required()->maxLength(255)->live(debounce: 800),
                        Fields::slug()->live(debounce: 800),
                        Textarea::make('description')
                            ->required()
                            ->rows(3)
                            ->maxLength(500)
                            ->unique(ignoreRecord: true)
                            ->live(debounce: 800)
                            ->helperText('Shown on the category page and used as its fallback meta description, so it must be unique.'),
                    ]),
                    Tab::make('Publish')->icon('heroicon-o-calendar-days')->schema([Fields::publishing(), Fields::language('name')]),
                ],
                input: fn (Get $get): SeoInput => SeoInput::fromPlainText(
                    $get('name'),
                    $get('meta_title'),
                    $get('meta_description'),
                    $get('focus_keyword'),
                    $get('slug'),
                    [(string) $get('description')],
                ),
                path: fn (Get $get): ?string => filled($get('slug')) ? '/blog/category/'.$get('slug') : null,
            ),
        ]);
    }
}
