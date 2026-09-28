<?php

namespace App\Filament\Resources\Settings\Schemas;

use App\Filament\Support\BrandFields;
use App\Filament\Support\Fields;
use App\Filament\Support\SeoFields;
use App\Support\Seo\SeoInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            SeoFields::layout(
                tabs: [
                    Tab::make('Profile')->icon('heroicon-o-user')->schema([
                        Section::make()->columns(2)->schema([
                            TextInput::make('name')->label('Your name')->required()->maxLength(255)->live(debounce: 800),
                            TextInput::make('headline')->required()->maxLength(255)->live(debounce: 800),
                            Textarea::make('bio_short')->label('Short bio')->rows(3)->columnSpanFull()->live(debounce: 800)
                                ->helperText('Shown under the logo in the footer and used as the default meta description.'),
                            Fields::image('portrait', 'Portrait', required: true),
                            Fields::altText('portrait_alt'),
                            SpatieMediaLibraryFileUpload::make('cv')->label('CV (PDF)')->collection('cv')
                                ->acceptedFileTypes(['application/pdf'])->maxSize(10240)
                                ->helperText('Target of the "Download CV" buttons.'),
                        ]),
                        Section::make('Stats')->schema([
                            Repeater::make('stats')->hiddenLabel()
                                ->schema([
                                    Fields::icon(),
                                    TextInput::make('value')->required()->maxLength(32),
                                    TextInput::make('label')->required()->maxLength(255),
                                ])
                                ->columns(3)
                                ->maxItems(4)
                                ->reorderable()
                                ->itemLabel(fn (array $state): ?string => $state['label'] ?? null),
                        ]),
                    ]),
                    Tab::make('Brand & tracking')->icon('heroicon-o-swatch')->schema(BrandFields::schema()),
                    Tab::make('Contact')->icon('heroicon-o-envelope')->schema([
                        Section::make()->columns(2)->schema([
                            TextInput::make('email')->email()->required()->maxLength(255),
                            TextInput::make('phone')->tel()->maxLength(50),
                            TextInput::make('whatsapp')->label('WhatsApp')->tel()->maxLength(50),
                            TextInput::make('city')->maxLength(255),
                            TextInput::make('working_hours')->maxLength(255),
                            TextInput::make('response_time')->maxLength(255),
                        ]),
                        Section::make('Social links')->columns(2)->schema([
                            TextInput::make('socials.github')->label('GitHub')->url(),
                            TextInput::make('socials.linkedin')->label('LinkedIn')->url(),
                            TextInput::make('socials.x')->label('X')->url(),
                            TextInput::make('socials.instagram')->label('Instagram')->url(),
                        ]),
                        Section::make('Contact form options')->schema([
                            TagsInput::make('contact_options.needs')->label('"What do you need?" choices'),
                            TagsInput::make('contact_options.budgets')->label('Budget choices'),
                            TagsInput::make('contact_options.timelines')->label('Timeline choices'),
                        ]),
                    ]),
                    Tab::make('Blog')->icon('heroicon-o-newspaper')->schema([
                        Section::make()->schema([
                            TagsInput::make('popular_searches')->helperText('Suggested searches on the "no results" page.'),
                        ]),
                    ]),
                ],
                input: fn (Get $get): SeoInput => SeoInput::fromPlainText(
                    trim("Hi, I'm {$get('name')}. {$get('headline')}"),
                    $get('meta_title'),
                    $get('meta_description'),
                    $get('focus_keyword'),
                    '',
                    [(string) $get('bio_short')],
                ),
                advanced: false,
            ),
        ]);
    }
}
