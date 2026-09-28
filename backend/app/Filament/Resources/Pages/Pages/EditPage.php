<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Filament\Support\Fields;
use App\Models\Page;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Fields::preview('')->url(fn (Page $record) => $record->path() === null ? null : rtrim(config('portfolio.frontend_url'), '/').$record->path(), shouldOpenInNewTab: true)
                ->visible(fn (Page $record) => $record->path() !== null),
        ];
    }
}
