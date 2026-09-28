<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Filament\Support\Fields;
use App\Filament\Support\RichBodyField;
use App\Filament\Support\SeoFields;
use App\Support\RichBody;
use App\Support\Seo\SeoInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            SeoFields::layout(
                tabs: [
                    Tab::make('Overview')->icon('heroicon-o-briefcase')->schema([
                        TextInput::make('title')->label('Title (H1)')->required()->maxLength(255)->live(debounce: 800),
                        Fields::slug()->live(debounce: 800),
                        Textarea::make('summary')->required()->rows(2)->maxLength(300)
                            ->helperText('Shown on the project cards.')->live(debounce: 800),
                        RichBodyField::compact('lead')->label('Intro')->helperText('Intro paragraph on the case-study page.'),
                        Grid::make(2)->schema([
                            TextInput::make('client')->maxLength(255),
                            TextInput::make('role')->label('My role')->maxLength(255),
                            TextInput::make('timeline')->maxLength(255),
                            TextInput::make('year')->numeric()->minValue(2000)->maxValue(2100),
                        ]),
                        TextInput::make('live_url')->url()->maxLength(255)
                            ->helperText('The "Visit Live Site" button only shows when this is set.'),
                    ]),
                    Tab::make('Story')->icon('heroicon-o-book-open')->schema([
                        RichBodyField::compact('challenge'),
                        RichBodyField::compact('solution'),
                        RichBodyField::compact('result'),
                    ]),
                    Tab::make('Features & results')->icon('heroicon-o-sparkles')->schema([
                        Repeater::make('features')
                            ->schema([
                                Fields::icon(),
                                TextInput::make('title')->required()->maxLength(255),
                                Textarea::make('text')->required()->rows(2),
                            ])
                            ->columns(3)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
                        TagsInput::make('stack')->label('Built with'),
                        Repeater::make('metrics')
                            ->schema([
                                TextInput::make('value')->required()->maxLength(32),
                                TextInput::make('label')->required()->maxLength(255),
                            ])
                            ->columns(2)
                            ->maxItems(3)
                            ->itemLabel(fn (array $state): ?string => $state['value'] ?? null),
                    ]),
                    Tab::make('Screens')->icon('heroicon-o-photo')->schema([
                        Section::make('Main screenshot')->schema([
                            Fields::image('cover', 'Cover', required: true),
                            Fields::altText('cover_alt'),
                        ]),
                        Repeater::make('screens')
                            ->relationship()
                            ->orderColumn('sort_order')
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('image')->collection('image')->image()->imageEditor()->maxSize(10240)->required(),
                                TextInput::make('caption')->maxLength(255),
                                Fields::altText('alt'),
                            ])
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['caption'] ?? null),
                    ]),
                    Tab::make('Relations')->icon('heroicon-o-link')->schema([
                        Select::make('service_id')->relationship('service', 'nav_label')->searchable()->preload()
                            ->helperText('Links the project page to its service landing.'),
                        Select::make('testimonial_id')->relationship('testimonial', 'name')->searchable()->preload(),
                        Toggle::make('featured')->helperText('Shown in Featured Projects on the home page.'),
                    ]),
                    Tab::make('Publish')->icon('heroicon-o-calendar-days')->schema([Fields::publishing()]),
                ],
                input: fn (Get $get): SeoInput => SeoInput::fromPlainText(
                    $get('title'),
                    $get('meta_title'),
                    $get('meta_description'),
                    $get('focus_keyword'),
                    $get('slug'),
                    [$get('summary'), RichBody::plain($get('lead')), RichBody::plain($get('challenge')), RichBody::plain($get('solution')), RichBody::plain($get('result'))],
                ),
                path: fn (Get $get): ?string => filled($get('slug')) ? '/projects/'.$get('slug') : null,
            ),
        ]);
    }
}
