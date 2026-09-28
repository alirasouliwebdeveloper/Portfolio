<?php

namespace App\Filament\Resources\Faqs\Schemas;

use App\Filament\Support\RichBodyField;
use Filament\Forms\Components\Select;
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
                RichBodyField::compact('answer')->required(),
                Select::make('scope')->options(['contact' => 'Contact page'])->default('contact')->required(),
            ]),
        ]);
    }
}
