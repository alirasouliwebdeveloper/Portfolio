<?php

namespace App\Filament\Resources\ContactMessages\Tables;

use App\Enums\ContactMessageStatus;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContactMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->dateTime('M j, H:i')->sortable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('need')->toggleable(),
                TextColumn::make('budget')->toggleable(),
                TextColumn::make('status')->badge(),
            ])
            ->filters([SelectFilter::make('status')->options(ContactMessageStatus::class)])
            ->defaultSort('created_at', 'desc')
            ->recordActions([ViewAction::make()]);
    }
}
