<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LowSeoContent extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Articles that need SEO work';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Post::query()->where(fn ($query) => $query->whereNull('seo_score')->orWhere('seo_score', '<', 80))->orderBy('seo_score'))
            ->columns([
                TextColumn::make('title')->limit(70)->searchable(),
                TextColumn::make('focus_keyword')->label('Focus keyword')->placeholder('not set'),
                TextColumn::make('seo_score')->label('SEO')->badge()
                    ->color(fn (?int $state): string => match (true) {
                        $state === null => 'gray',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    }),
            ])
            ->recordActions([
                Action::make('improve')->label('Improve')->icon('heroicon-o-pencil-square')
                    ->url(fn (Post $record) => PostResource::getUrl('edit', ['record' => $record])),
            ])
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('Every article scores 80 or higher');
    }
}
