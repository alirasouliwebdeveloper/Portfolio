<?php

namespace App\Filament\Support;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

/** Brand and site-wide options, shared by the Settings page and the global settings overlay. */
final class BrandFields
{
    /** @return list<Section> */
    public static function schema(): array
    {
        return [
            Section::make('Brand')
                ->description('Shown in the site header, footer and browser tab.')
                ->columns(2)
                ->schema([
                    TextInput::make('brand_name')->label('Site name')->required()->maxLength(255),
                    TextInput::make('tagline')->maxLength(255),
                    SpatieMediaLibraryFileUpload::make('logo')->label('Logo')->collection('logo')
                        ->image()->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/webp', 'image/jpeg'])->maxSize(2048)
                        ->helperText('Used on the dark site header and footer. SVG or PNG with a transparent background works best. Empty = the </> mark with the site name.'),
                    SpatieMediaLibraryFileUpload::make('logo_admin')->label('Admin panel logo')->collection('logo_admin')
                        ->image()->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/webp', 'image/jpeg'])->maxSize(2048)
                        ->helperText('Optional dark-on-light version for this admin panel. Empty = text logo.'),
                    SpatieMediaLibraryFileUpload::make('favicon')->label('Favicon')->collection('favicon')
                        ->image()->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/x-icon', 'image/vnd.microsoft.icon'])->maxSize(512)
                        ->helperText('Square PNG, SVG or ICO (at least 64×64 px).'),
                    Fields::image('og_image', 'Default social sharing image')->helperText('Used when a page has no image of its own (1200×630 works best).'),
                    TextInput::make('footer_text')->label('Footer text')->maxLength(255)
                        ->helperText('Shown next to © and the year in the footer bottom bar.'),
                ]),
            Section::make('Tracking & visibility')
                ->columns(2)
                ->schema([
                    TextInput::make('ga_measurement_id')->label('Google Analytics ID')->placeholder('G-XXXXXXXXXX')->maxLength(32)
                        ->rules(['nullable', 'regex:/^G-[A-Z0-9]+$/'])->validationMessages(['regex' => 'Use the format G-XXXXXXXXXX.']),
                    TextInput::make('gsc_verification')->label('Search Console verification code')->maxLength(255)
                        ->helperText('The content value of the google-site-verification meta tag.'),
                    TextInput::make('twitter_handle')->label('X (Twitter) handle')->prefix('@')->maxLength(64),
                    Toggle::make('site_noindex')->label('Hide the whole site from search engines')
                        ->helperText('Turn on for staging. Adds noindex everywhere and disallows crawling in robots.txt.')->inline(false),
                ]),
        ];
    }
}
