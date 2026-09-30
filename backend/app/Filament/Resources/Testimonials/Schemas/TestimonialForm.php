<?php

namespace App\Filament\Resources\Testimonials\Schemas;

use App\Filament\Support\Fields;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                Textarea::make('quote')->required()->rows(4),
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('initials')->required()->maxLength(4)
                    ->helperText('Shown in the avatar circle when there is no photo.'),
                TextInput::make('role')->maxLength(255),
                TextInput::make('company')->maxLength(255),
                Select::make('rating')->options(array_combine(range(1, 5), range(1, 5)))->default(5)->required(),
                Toggle::make('featured')->helperText('Shown on the home page.'),
                Fields::image('avatar', 'Photo (optional)'),
            ])->columns(2),
            Fields::language('name'),
        ]);
    }
}
