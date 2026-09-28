<?php

namespace App\Filament\Resources\Experiences\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ExperienceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->schema([
                TextInput::make('role')->required()->maxLength(255),
                TextInput::make('company')->required()->maxLength(255),
                TextInput::make('start_year')->numeric()->required()->minValue(1990)->maxValue(2100),
                TextInput::make('end_year')->numeric()->minValue(1990)->maxValue(2100)
                    ->gte('start_year')
                    ->helperText('Leave empty for a current position (shown as "Present").'),
                Textarea::make('description')->rows(3)->columnSpanFull(),
            ]),
        ]);
    }
}
