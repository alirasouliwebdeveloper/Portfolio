<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Support\Fields;
use App\Filament\Support\RichBodyField;
use App\Filament\Support\SeoFields;
use App\Models\Page;
use App\Models\Service;
use App\Support\RichBody;
use App\Support\Seo\SeoInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PageForm
{
    /** @return list<string> every text (rich documents flattened) inside the structured content */
    private static function texts(mixed $value): array
    {
        $out = [];
        $walk = function (mixed $item) use (&$walk, &$out): void {
            if (is_string($item)) {
                $out[] = $item;
            } elseif (is_array($item) && ($item['type'] ?? null) === 'doc') {
                $out[] = RichBody::plain($item);
            } elseif (is_array($item)) {
                array_map($walk, $item);
            }
        };
        $walk($value);

        return $out;
    }

    public static function configure(Schema $schema): Schema
    {
        $is = fn (string ...$keys) => fn (?Page $record): bool => $record !== null && in_array($record->key, $keys, true);

        return $schema->components([
            SeoFields::layout(
                tabs: [
                    Tab::make('Page')->icon('heroicon-o-document')->schema([
                        TextInput::make('title')->label('Title (H1)')->required()->maxLength(255)->live(debounce: 800)
                            ->helperText(fn (?Page $record): string => $record?->key === 'home'
                                ? 'The hero shows this as the page heading.'
                                : 'The heading at the top of the page.'),
                    ]),
                    Tab::make('Hero & intro')->icon('heroicon-o-home')->visible($is('home'))->schema([
                        Section::make('Hero')->columns(2)->schema([
                            TextInput::make('content.hero.chip')->maxLength(80),
                            TextInput::make('content.hero.greeting')->maxLength(80)->helperText('Shown before your name in the heading.'),
                            Textarea::make('content.hero.lead')->rows(2)->columnSpanFull()->helperText('The sentence under the sub-headline.'),
                            TextInput::make('content.hero.tech_label')->label('Technologies label')->maxLength(80),
                            TagsInput::make('content.hero.technologies')->label('Technologies')->helperText('Icons shown under the hero. Known: HTML, JavaScript, TypeScript, React, Node.js, Git, Laravel, PHP, Docker.')->columnSpanFull(),
                            TagsInput::make('content.hero.code_stack')->label('Code card: stack'),
                            TextInput::make('content.hero.code_passion')->label('Code card: passion')->maxLength(120),
                        ]),
                        Section::make('About teaser')->schema([
                            TextInput::make('content.about_teaser.eyebrow')->maxLength(80),
                            TextInput::make('content.about_teaser.heading')->maxLength(255),
                            RichBodyField::compact('content.about_teaser.text')->label('Text'),
                        ]),
                    ]),
                    Tab::make('Sections')->icon('heroicon-o-queue-list')->visible($is('home'))->schema([
                        Section::make('What I build with')->schema([
                            TextInput::make('content.stack.eyebrow')->maxLength(80),
                            TextInput::make('content.stack.heading')->maxLength(255),
                            Textarea::make('content.stack.text')->rows(2),
                            Repeater::make('content.stack.groups')->label('Cards')
                                ->schema([
                                    Fields::icon(),
                                    TextInput::make('title')->required()->maxLength(80),
                                    Textarea::make('text')->required()->rows(2),
                                    TagsInput::make('tags'),
                                    Select::make('service_slug')->label('Links to service')
                                        ->options(fn () => Service::query()->orderBy('sort_order')->pluck('nav_label', 'slug')->all())->searchable(),
                                ])
                                ->columns(2)->maxItems(4)->reorderable()->collapsible()
                                ->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
                        ]),
                        Section::make('Section headings')->columns(2)->schema([
                            TextInput::make('content.projects.eyebrow')->label('Projects: eyebrow'),
                            TextInput::make('content.projects.heading')->label('Projects: heading'),
                            TextInput::make('content.testimonials.eyebrow')->label('Testimonials: eyebrow'),
                            TextInput::make('content.testimonials.heading')->label('Testimonials: heading'),
                            TextInput::make('content.blog.eyebrow')->label('Blog: eyebrow'),
                            TextInput::make('content.blog.heading')->label('Blog: heading'),
                        ]),
                        Section::make('Contact teaser')->schema([
                            TextInput::make('content.contact.eyebrow'),
                            TextInput::make('content.contact.heading'),
                            Textarea::make('content.contact.text')->rows(2),
                        ]),
                    ]),
                    Tab::make('About page')->icon('heroicon-o-user')->visible($is('about'))->schema([
                        Section::make('Hero')->schema([
                            TextInput::make('content.hero.chip')->maxLength(80),
                            RichBodyField::compact('content.hero.text')->label('Text'),
                        ]),
                        Section::make('Sections')->columns(2)->schema([
                            TextInput::make('content.how.eyebrow')->label('How I work: eyebrow'),
                            TextInput::make('content.how.heading')->label('How I work: heading'),
                            Textarea::make('content.how.text')->label('How I work: intro')->rows(2)->columnSpanFull(),
                            TextInput::make('content.experience.eyebrow')->label('Experience: eyebrow'),
                            TextInput::make('content.experience.heading')->label('Experience: heading'),
                        ]),
                        Section::make('Toolbox')->schema([
                            TextInput::make('content.toolbox.eyebrow'),
                            TextInput::make('content.toolbox.heading'),
                            Repeater::make('content.toolbox.groups')->label('Groups')
                                ->schema([Fields::icon(), TextInput::make('title')->required()->maxLength(80), TagsInput::make('tags')])
                                ->columns(3)->maxItems(6)->reorderable()->collapsible()
                                ->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
                        ]),
                        Section::make('Service pages: trust row')->description('Three highlights shown under the hero of every service page.')->schema([
                            Repeater::make('content.trust')->hiddenLabel()
                                ->schema([Fields::icon(), TextInput::make('value')->required()->maxLength(40), TextInput::make('label')->required()->maxLength(80)])
                                ->columns(3)->maxItems(3)->reorderable()
                                ->itemLabel(fn (array $state): ?string => $state['value'] ?? null),
                        ]),
                        Section::make('Call to action')->schema([
                            TextInput::make('content.cta.heading'),
                            Textarea::make('content.cta.text')->rows(2),
                            TextInput::make('content.cta.button')->maxLength(60),
                        ]),
                    ]),
                    Tab::make('Contact page')->icon('heroicon-o-envelope')->visible($is('contact'))->schema([
                        Section::make('Header')->schema([
                            TextInput::make('content.hero.eyebrow')->maxLength(80),
                            Textarea::make('content.hero.text')->rows(2),
                        ]),
                        Section::make('FAQ heading')->schema([
                            TextInput::make('content.faq.eyebrow')->maxLength(80),
                            TextInput::make('content.faq.heading')->maxLength(255),
                            Textarea::make('content.faq.text')->rows(2),
                        ]),
                    ]),
                    Tab::make('Blog page')->icon('heroicon-o-newspaper')->visible($is('blog'))->schema([
                        TextInput::make('content.eyebrow')->maxLength(80),
                        Textarea::make('content.description')->rows(3),
                    ]),
                    Tab::make('404 page')->icon('heroicon-o-exclamation-triangle')->visible($is('not_found'))->schema([
                        Textarea::make('content.text')->rows(3),
                    ]),
                    Tab::make('Content')->icon('heroicon-o-document-text')->visible($is('privacy', 'terms'))->schema([
                        RichBodyField::make('body')->required(),
                    ]),
                ],
                input: fn (Get $get): SeoInput => SeoInput::fromPlainText(
                    $get('title'),
                    $get('meta_title'),
                    $get('meta_description'),
                    $get('focus_keyword'),
                    '',
                    self::texts($get('content')),
                ),
                path: fn (Get $get): ?string => null,
            ),
        ]);
    }
}
