<?php

namespace App\Filament\Resources\Settings\Schemas;

use App\Filament\Support\BrandFields;
use App\Filament\Support\Fields;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()->columnSpanFull()->tabs([
                Tab::make('Profile')->icon('heroicon-o-user')->schema([
                    Section::make()->columns(2)->schema([
                        TextInput::make('name')->label('Your name')->required()->maxLength(255),
                        TextInput::make('headline')->required()->maxLength(255),
                        Textarea::make('bio_short')->label('Short bio')->rows(3)->columnSpanFull()
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
                Tab::make('Email')->icon('heroicon-o-at-symbol')->schema([
                    Section::make('Contact form notifications')
                        ->description('Every contact-form message is emailed to the contact email (Contact tab) and to this address.')
                        ->schema([
                            TextInput::make('notification_email')->email()->maxLength(255)
                                ->placeholder('ali.rasouli.webdeveloper@gmail.com'),
                        ]),
                    Section::make('SMTP server')
                        ->description('Leave the host empty to use the server defaults (.env). Use "Send test email" at the top to check these.')
                        ->columns(2)
                        ->schema([
                            TextInput::make('mail_host')->label('Host')->maxLength(255)->placeholder('mail.alirasouli.ir'),
                            TextInput::make('mail_port')->label('Port')->numeric()->minValue(1)->maxValue(65535)->placeholder('465'),
                            Select::make('mail_encryption')->label('Encryption')
                                ->options(['ssl' => 'SSL (port 465)', 'tls' => 'TLS / STARTTLS (port 587)'])
                                ->placeholder('None'),
                            TextInput::make('mail_username')->label('Username')->maxLength(255)->autocomplete('off'),
                            TextInput::make('mail_password')->label('Password')->password()->revealable()
                                ->autocomplete('new-password')
                                // Never sent back to the browser; an empty field keeps the saved password.
                                ->formatStateUsing(fn () => null)
                                ->dehydrated(fn (?string $state) => filled($state))
                                ->helperText(fn ($record) => filled($record?->mail_password) ? 'Saved. Leave empty to keep it.' : null),
                            TextInput::make('mail_from_address')->label('From address')->email()->maxLength(255),
                            TextInput::make('mail_from_name')->label('From name')->maxLength(255),
                        ]),
                ]),
                Tab::make('Blog')->icon('heroicon-o-newspaper')->schema([
                    Section::make()->schema([
                        TagsInput::make('popular_searches')->helperText('Suggested searches on the "no results" page.'),
                    ]),
                ]),
                Tab::make('Persian (فارسی)')->icon('heroicon-o-language')->schema([
                    Section::make()
                        ->description('Only the handful of fields that read as prose — everything else (name, contact details, socials) is the same in both languages. Empty = falls back to the English value.')
                        ->columns(2)
                        ->schema([
                            TextInput::make('headline_fa')->label('Headline')->maxLength(255),
                            TextInput::make('tagline_fa')->label('Tagline')->maxLength(255),
                            Textarea::make('bio_short_fa')->label('Short bio')->rows(3)->columnSpanFull(),
                            TextInput::make('footer_text_fa')->label('Footer text')->maxLength(255),
                        ]),
                    Section::make('Stats')->schema([
                        Repeater::make('stats_fa')->hiddenLabel()
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
            ]),
        ]);
    }
}
