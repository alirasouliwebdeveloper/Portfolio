<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Filament\Support\Fields;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('name')->required()->maxLength(255),
                Fields::slug(),
                Textarea::make('description')
                    ->required()
                    ->rows(3)
                    ->maxLength(500)
                    ->unique(ignoreRecord: true)
                    ->helperText('Shown on the category page and used as its meta description, so it must be unique.'),
            ]),
            Fields::publishing(),
            Fields::seo(),
        ]);
    }
}
