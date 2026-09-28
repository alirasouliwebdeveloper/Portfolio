<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestMessages extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Latest contact messages';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => ContactMessage::query()->latest())
            ->columns([
                TextColumn::make('created_at')->dateTime('M j, H:i'),
                TextColumn::make('name'),
                TextColumn::make('need')->placeholder('—'),
                TextColumn::make('status')->badge(),
            ])
            ->recordActions([
                Action::make('open')->label('Open')->icon('heroicon-o-eye')
                    ->url(fn (ContactMessage $record) => ContactMessageResource::getUrl('view', ['record' => $record])),
            ])
            ->paginated(false)
            ->emptyStateHeading('No messages yet');
    }
}
