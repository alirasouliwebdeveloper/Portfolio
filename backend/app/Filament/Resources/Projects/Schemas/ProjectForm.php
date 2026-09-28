<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Filament\Support\Fields;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
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

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 3])->columnSpanFull()->schema([
                Group::make([
                    Tabs::make()->tabs([
                        Tab::make('Overview')->schema([
                            TextInput::make('title')->required()->maxLength(255),
                            Fields::slug(),
                            Textarea::make('summary')->required()->rows(2)->maxLength(300)
                                ->helperText('Shown on the project cards.'),
                            Textarea::make('lead')->rows(3)->helperText('Intro paragraph on the case-study page.'),
                            Grid::make(2)->schema([
                                TextInput::make('client')->maxLength(255),
                                TextInput::make('role')->label('My role')->maxLength(255),
                                TextInput::make('timeline')->maxLength(255),
                                TextInput::make('year')->numeric()->minValue(2000)->maxValue(2100),
                            ]),
                            TextInput::make('live_url')->url()->maxLength(255)
                                ->helperText('The "Visit Live Site" button only shows when this is set.'),
                        ]),
                        Tab::make('Story')->schema([
                            Textarea::make('challenge')->rows(4),
                            Textarea::make('solution')->rows(4),
                            Textarea::make('result')->rows(4),
                        ]),
                        Tab::make('Features & results')->schema([
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
                        Tab::make('Screens')->schema([
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
                    ]),
                ])->columnSpan(['lg' => 2]),
                Group::make([
                    Fields::publishing(),
                    Section::make('Relations')->schema([
                        Select::make('service_id')->relationship('service', 'nav_label')->searchable()->preload()
                            ->helperText('Links the project page to its service landing.'),
                        Select::make('testimonial_id')->relationship('testimonial', 'name')->searchable()->preload(),
                        Toggle::make('featured')->helperText('Shown in Featured Projects on the home page.'),
                    ]),
                    Section::make('Main screenshot')->schema([
                        Fields::image('cover', 'Cover', required: true),
                        Fields::altText('cover_alt'),
                    ]),
                    Fields::seo(),
                ])->columnSpan(['lg' => 1]),
            ]),
        ]);
    }
}
