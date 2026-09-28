<?php

namespace App\Filament\Support;

use App\Enums\PublishStatus;
use App\Support\IconSet;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Model;

/** Field groups shared by several Filament resources. */
final class Fields
{
    public static function icon(string $name = 'icon'): Select
    {
        return Select::make($name)
            ->options(IconSet::options())
            ->searchable()
            ->required();
    }

    public static function slug(): TextInput
    {
        return TextInput::make('slug')
            ->maxLength(255)
            ->unique(ignoreRecord: true)
            ->rules(['regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'])
            ->validationMessages(['regex' => 'Use lowercase letters, numbers and hyphens only.'])
            ->placeholder('Generated from the title')
            ->helperText('Public URL identifier. Changing it later breaks existing links.')
            ->dehydrateStateUsing(fn (?string $state) => filled($state) ? $state : null);
    }

    public static function image(string $collection, string $label, bool $required = false): SpatieMediaLibraryFileUpload
    {
        return SpatieMediaLibraryFileUpload::make($collection)
            ->label($label)
            ->collection($collection)
            ->image()
            ->imageEditor()
            ->maxSize(10240)
            ->required($required);
    }

    public static function altText(string $name = 'cover_alt', bool $required = true): TextInput
    {
        return TextInput::make($name)
            ->label('Image alt text')
            ->helperText('Describes the image for screen readers and search engines.')
            ->maxLength(255)
            ->required($required);
    }

    public static function publishing(): Section
    {
        return Section::make('Publishing')
            ->schema([
                Select::make('status')
                    ->options(PublishStatus::class)
                    ->default(PublishStatus::Draft)
                    ->required(),
                DateTimePicker::make('published_at')
                    ->helperText('Empty = publish immediately once the status is Published. A future date schedules it.'),
            ]);
    }

    public static function seo(): Section
    {
        return Section::make('SEO')
            ->description('Optional. Empty fields fall back to the title and summary.')
            ->collapsed()
            ->schema([
                TextInput::make('meta_title')->maxLength(255),
                Textarea::make('meta_description')->rows(2)->maxLength(320),
                self::image('og_image', 'Open Graph image'),
            ]);
    }

    /** Header action opening the public Next.js page of a record. */
    public static function preview(string $pathPrefix): Action
    {
        return Action::make('preview')
            ->icon('heroicon-o-arrow-top-right-on-square')
            ->color('gray')
            ->url(fn (Model $record) => rtrim(config('portfolio.frontend_url'), '/').$pathPrefix.'/'.$record->slug, shouldOpenInNewTab: true);
    }
}
