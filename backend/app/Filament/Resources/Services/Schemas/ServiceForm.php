<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Filament\Support\Fields;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 3])->columnSpanFull()->schema([
                Group::make([
                    Tabs::make()->tabs([
                        Tab::make('Hero')->schema([
                            TextInput::make('title')->required()->maxLength(255)->helperText('Service name used in page titles.'),
                            TextInput::make('nav_label')->required()->maxLength(255)->helperText('Short label for footer and internal links.'),
                            Fields::slug(),
                            TextInput::make('h1')->label('H1 (keyword headline)')->required()->maxLength(255),
                            Textarea::make('lead')->required()->rows(3),
                            Fields::icon(),
                            Grid::make(2)->schema([
                                TextInput::make('floating_metric.value')->label('Floating metric value')->maxLength(32),
                                TextInput::make('floating_metric.label')->label('Floating metric label')->maxLength(255),
                            ]),
                        ]),
                        Tab::make('Is this for you?')->schema([
                            self::cards('pains'),
                        ]),
                        Tab::make("What's included")->schema([
                            self::cards('offers'),
                        ]),
                        Tab::make('The stack')->schema([
                            TextInput::make('why.title')->maxLength(255),
                            Textarea::make('why.text')->rows(4),
                            Repeater::make('why.points')->label('Checklist')->simple(
                                TextInput::make('point')->required()->maxLength(255),
                            ),
                            TagsInput::make('stack')->label('Tools'),
                        ]),
                        Tab::make('Pricing')->schema([
                            Repeater::make('tiers')
                                ->schema([
                                    TextInput::make('name')->required()->maxLength(64),
                                    TextInput::make('price_from')->required()->maxLength(32),
                                    TextInput::make('subtitle')->maxLength(255),
                                    Repeater::make('items')->simple(TextInput::make('item')->required()->maxLength(255)),
                                    Toggle::make('highlighted')->label('Most popular'),
                                ])
                                ->columns(3)
                                ->maxItems(3)
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
                        ]),
                        Tab::make('FAQ')->schema([
                            Repeater::make('faq')
                                ->schema([
                                    TextInput::make('question')->required()->maxLength(255),
                                    Textarea::make('answer')->required()->rows(3),
                                ])
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => $state['question'] ?? null),
                        ]),
                    ]),
                ])->columnSpan(['lg' => 2]),
                Group::make([
                    Fields::publishing(),
                    Section::make('Related content')->schema([
                        Select::make('related_project_id')->relationship('relatedProject', 'title')->searchable()->preload(),
                        Select::make('related_category_id')->relationship('relatedCategory', 'name')->searchable()->preload(),
                        Select::make('posts')->label('Related articles')->relationship('posts', 'title')->multiple()->searchable()->preload()
                            ->helperText('Empty = newest posts of the related category.'),
                    ]),
                    Section::make('Hero image')->schema([
                        Fields::image('hero_image', 'Hero image', required: true),
                        Fields::altText('hero_image_alt'),
                    ]),
                    Fields::seo(),
                ])->columnSpan(['lg' => 1]),
            ]),
        ]);
    }

    private static function cards(string $name): Repeater
    {
        return Repeater::make($name)
            ->schema([
                Fields::icon(),
                TextInput::make('title')->required()->maxLength(255),
                Textarea::make('text')->required()->rows(2),
            ])
            ->columns(3)
            ->reorderable()
            ->collapsible()
            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null);
    }
}
