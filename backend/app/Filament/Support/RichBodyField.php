<?php

namespace App\Filament\Support;

use App\Support\ImageOptimizer;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\ToolbarButtonGroup;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * The one WordPress-style editor used for every long text on the site:
 * formatting, colours, sizes, direction, lists, tables, FAQ (details), links
 * with nofollow/title, and images with alt/title/size/alignment. The page's
 * H1 is its title, so the editor offers H2–H4 only.
 */
class RichBodyField
{
    /** Lighter toolbar for short rich fields (intros, FAQ answers). */
    public static function compact(string $name, string $directory = 'content'): RichEditor
    {
        return self::make($name, $directory)
            ->toolbarButtons([
                ['bold', 'italic', 'underline', 'link'],
                ['bulletList', 'orderedList'],
                ['clearFormatting', 'undo', 'redo'],
            ])
            ->extraInputAttributes(['style' => 'min-height: 9rem']);
    }

    public static function make(string $name = 'body', string $directory = 'content'): RichEditor
    {
        return RichEditor::make($name)
            ->json()
            ->live(onBlur: true)
            ->toolbarButtons([
                [ToolbarButtonGroup::make('Paragraph', ['paragraph', 'h2', 'h3', 'h4'])->textualButtons()],
                ['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript', 'code'],
                ['highlight', 'small', 'lead', 'textColor', 'fontSize'],
                [ToolbarButtonGroup::make('Alignment', ['alignStart', 'alignCenter', 'alignEnd', 'alignJustify']), 'directionRtl', 'directionLtr', 'directionAuto'],
                ['bulletList', 'orderedList', 'indent', 'outdent'],
                ['blockquote', 'codeBlock', 'horizontalRule', 'details'],
                ['link', 'linkSettings'],
                ['attachFiles', 'imageSettings', 'table'],
                ['clearFormatting', 'undo', 'redo'],
            ])
            ->floatingToolbars([
                'table' => [
                    'tableAddColumnBefore', 'tableAddColumnAfter', 'tableDeleteColumn',
                    'tableAddRowBefore', 'tableAddRowAfter', 'tableDeleteRow',
                    'tableMergeCells', 'tableSplitCell',
                    'tableToggleHeaderRow', 'tableToggleHeaderCell',
                    'tableDelete',
                ],
            ])
            ->plugins([WordPressEditorPlugin::make()])
            ->customTextColors()
            ->fileAttachmentsDirectory($directory)
            ->fileAttachmentsAcceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
            ->fileAttachmentsMaxSize(5120)
            ->saveUploadedFileAttachmentUsing(
                fn (TemporaryUploadedFile $file, RichEditor $component) => ImageOptimizer::store(
                    $file,
                    $component->getFileAttachmentsDirectory(),
                    $component->getFileAttachmentsDiskName(),
                    $component->getFileAttachmentsVisibility(),
                ),
            )
            ->columnSpanFull();
    }
}
