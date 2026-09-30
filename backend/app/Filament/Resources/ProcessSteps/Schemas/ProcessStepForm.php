<?php

namespace App\Filament\Resources\ProcessSteps\Schemas;

use App\Filament\Support\Fields;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProcessStepForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                Fields::icon(),
                TextInput::make('title')->required()->maxLength(255),
                Textarea::make('text')->required()->rows(3),
            ]),
            Fields::language('title'),
        ]);
    }
}
