<?php

namespace App\Filament\Resources\ContactMessages\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

class ContactMessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Sender')->columns(3)->schema([
                TextEntry::make('name'),
                TextEntry::make('email')->copyable(),
                TextEntry::make('phone')->placeholder('—'),
                TextEntry::make('company')->placeholder('—'),
                TextEntry::make('status')->badge(),
                TextEntry::make('created_at')->dateTime(),
            ]),
            Section::make('Request')->columns(3)->schema([
                TextEntry::make('need')->placeholder('—'),
                TextEntry::make('budget')->placeholder('—'),
                TextEntry::make('timeline')->placeholder('—'),
                TextEntry::make('service_slug')->label('From service page')->placeholder('—'),
                TextEntry::make('message')->columnSpanFull()->prose(),
            ]),
            Section::make('Files')->schema([
                RepeatableEntry::make('uploads')->hiddenLabel()->contained(false)->columns(2)->schema([
                    TextEntry::make('original_name')->label('File'),
                    TextEntry::make('size')->formatStateUsing(fn ($state) => Number::fileSize((int) $state)),
                ]),
            ])->collapsible(),
        ]);
    }
}
