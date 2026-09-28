<?php

namespace App\Filament\Resources\ContactMessages\Pages;

use App\Enums\ContactMessageStatus;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewContactMessage extends ViewRecord
{
    protected static string $resource = ContactMessageResource::class;

    protected function getHeaderActions(): array
    {
        return array_map(
            fn (ContactMessageStatus $status) => Action::make('mark_'.$status->value)
                ->label('Mark as '.$status->value)
                ->color($status->getColor())
                ->visible(fn () => $this->record->status !== $status)
                ->action(fn () => $this->record->update(['status' => $status])),
            ContactMessageStatus::cases(),
        );
    }
}
