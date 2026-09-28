<?php

namespace App\Filament\Resources\Redirects;

use App\Filament\Resources\Redirects\Pages\ManageRedirects;
use App\Models\Redirect;
use BackedEnum;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

/** Old URL -> new URL. Slug changes add rows here automatically; manual ones can be added too. */
class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    protected static ?int $navigationSort = 30;

    protected static ?string $recordTitleAttribute = 'from_path';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUturnRight;

    public static function form(Schema $schema): Schema
    {
        $path = fn (TextInput $input) => $input
            ->required()
            ->maxLength(255)
            ->startsWith('/')
            ->placeholder('/blog/some-post')
            ->dehydrateStateUsing(fn (string $state) => '/'.trim($state, '/'));

        return $schema->components([
            $path(TextInput::make('from_path')->label('Old path')->unique(ignoreRecord: true)->helperText('The address visitors and search engines still use.')),
            $path(TextInput::make('to_path')->label('New path')->different('from_path')->helperText('Where they should land instead.')),
            Select::make('status_code')->label('Type')->options([301 => '301 — moved permanently', 302 => '302 — temporary'])->default(301)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('from_path')->label('Old path')->searchable(),
                TextColumn::make('to_path')->label('New path')->searchable(),
                TextColumn::make('status_code')->label('Type')->badge()->color('gray'),
                TextColumn::make('created_at')->since()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([CreateAction::make(), DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageRedirects::route('/')];
    }
}
