<?php

namespace App\Filament\Resources\Services\Tables;

use App\Enums\PublishStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nav_label')->label('Service')->searchable(),
                TextColumn::make('slug')->color('gray'),
                TextColumn::make('relatedProject.title')->label('Related project'),
                TextColumn::make('status')->badge(),
            ])
            ->filters([SelectFilter::make('status')->options(PublishStatus::class)])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
