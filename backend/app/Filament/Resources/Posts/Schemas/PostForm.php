<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Filament\Support\Fields;
use App\Filament\Support\RichBodyField;
use App\Filament\Support\SeoFields;
use App\Support\RichBody;
use App\Support\Seo\SeoInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            SeoFields::layout(
                tabs: [
                    Tab::make('Content')->icon('heroicon-o-document-text')->schema([
                        TextInput::make('title')->label('Title (H1)')->required()->maxLength(255)->live(debounce: 800),
                        Fields::slug()->live(debounce: 800),
                        Textarea::make('excerpt')
                            ->required()
                            ->rows(3)
                            ->maxLength(500)
                            ->live(debounce: 800)
                            ->hint(fn (?string $state): string => mb_strlen((string) $state).' characters — aim for 120–160'),
                        RichBodyField::make('body')
                            ->required()
                            ->helperText('The title above is the page H1, so the editor offers H2–H4. Reading time is calculated when you save.'),
                    ]),
                    Tab::make('Details')->icon('heroicon-o-tag')->schema([
                        Section::make('Organisation')->columns(2)->schema([
                            Select::make('category_id')->relationship('category', 'name')->required()->searchable()->preload(),
                            Select::make('tags')->relationship('tags', 'name')->multiple()->searchable()->preload()
                                ->createOptionForm([TextInput::make('name')->required()->maxLength(255)]),
                            Select::make('related_service_id')->label('Related service card')->relationship('relatedService', 'nav_label')
                                ->searchable()->preload()->helperText('Adds a "related service" link card inside the article.'),
                            Toggle::make('featured')->helperText('Shown as the featured post on the blog and category pages.')->inline(false),
                        ]),
                        Section::make('Cover image')->schema([
                            Fields::image('cover', 'Cover', required: true),
                            Fields::altText('cover_alt'),
                        ]),
                    ]),
                    Tab::make('Publish')->icon('heroicon-o-calendar-days')->schema([Fields::publishing()]),
                ],
                input: fn (Get $get): SeoInput => SeoInput::fromPage(
                    $get('title'),
                    $get('meta_title'),
                    $get('meta_description'),
                    $get('focus_keyword'),
                    $get('slug'),
                    RichBody::normalize($get('body')),
                    $get('excerpt'),
                ),
                path: fn (Get $get): ?string => filled($get('slug')) ? '/blog/'.$get('slug') : null,
            ),
        ]);
    }
}
