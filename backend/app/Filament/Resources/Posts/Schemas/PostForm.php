<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Filament\Support\Fields;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 3])->columnSpanFull()->schema([
                Group::make([
                    Section::make()->schema([
                        TextInput::make('title')->required()->maxLength(255),
                        Fields::slug(),
                        Textarea::make('excerpt')
                            ->required()
                            ->rows(3)
                            ->maxLength(500)
                            ->live(onBlur: true)
                            ->hint(fn (?string $state): string => mb_strlen((string) $state).' characters — aim for 120–160'),
                        RichEditor::make('body')
                            ->required()
                            ->toolbarButtons([
                                ['bold', 'italic', 'link'],
                                ['h2', 'h3'],
                                ['blockquote', 'codeBlock', 'bulletList', 'orderedList'],
                                ['attachFiles', 'table', 'horizontalRule'],
                                ['undo', 'redo'],
                            ])
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('post-images')
                            ->helperText('Reading time is calculated automatically when you save.'),
                    ]),
                ])->columnSpan(['lg' => 2]),
                Group::make([
                    Fields::publishing(),
                    Section::make('Organisation')->schema([
                        Select::make('category_id')->relationship('category', 'name')->required()->searchable()->preload(),
                        Select::make('tags')->relationship('tags', 'name')->multiple()->searchable()->preload()
                            ->createOptionForm([TextInput::make('name')->required()->maxLength(255)]),
                        Select::make('related_service_id')->label('Related service card')->relationship('relatedService', 'nav_label')
                            ->searchable()->preload()->helperText('Adds a "related service" link card inside the article.'),
                        Toggle::make('featured')->helperText('Shown as the featured post on the blog and category pages.'),
                    ]),
                    Section::make('Cover image')->schema([
                        Fields::image('cover', 'Cover', required: true),
                        Fields::altText('cover_alt'),
                    ]),
                    Fields::seo(),
                ])->columnSpan(['lg' => 1]),
            ]),
        ]);
    }
}
