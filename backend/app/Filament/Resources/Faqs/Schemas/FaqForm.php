<?php

namespace App\Filament\Resources\Faqs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('question')->required()->maxLength(255),
                Textarea::make('answer')->required()->rows(5),
                Select::make('scope')->options(['contact' => 'Contact page'])->default('contact')->required(),
            ]),
        ]);
    }
}
