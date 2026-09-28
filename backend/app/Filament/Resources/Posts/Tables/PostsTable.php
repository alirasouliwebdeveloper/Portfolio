<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Enums\PublishStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('cover')->collection('cover')->conversion('card')->imageHeight(40),
                TextColumn::make('title')->searchable()->sortable()->limit(60),
                TextColumn::make('category.name')->badge()->sortable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('seo_score')->label('SEO')->badge()
                    ->color(fn (?int $state): string => match (true) {
                        $state === null => 'gray',
                        $state >= 80 => 'success',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),
                IconColumn::make('featured')->boolean(),
                TextColumn::make('reading_time')->suffix(' min')->sortable(),
                TextColumn::make('published_at')->dateTime('M j, Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(PublishStatus::class),
                SelectFilter::make('category')->relationship('category', 'name'),
                TernaryFilter::make('featured'),
            ])
            ->defaultSort('published_at', 'desc')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
