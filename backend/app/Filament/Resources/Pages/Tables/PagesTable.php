<?php

namespace App\Filament\Resources\Pages\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')->label('Page')->badge()->color('gray')->sortable(),
                TextColumn::make('title')->label('Title (H1)')->limit(60),
                TextColumn::make('seo_score')->label('SEO')->badge()
                    ->color(fn (?int $state): string => match (true) {
                        $state === null => 'gray',
                        $state >= 80 => 'success',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('updated_at')->since(),
            ])
            ->defaultSort('id')
            ->paginated(false)
            ->recordActions([EditAction::make()]);
    }
}
