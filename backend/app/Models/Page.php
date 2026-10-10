<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\RegistersImageConversions;
use App\Models\Concerns\ScoresSeo;
use App\Support\RichBody;
use App\Support\Seo\SeoInput;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;

/**
 * A fixed page of the site (home, about, contact, blog, projects, 404, privacy, terms): structured copy in
 * `content`, an optional rich `body`, and its own SEO fields. `key` exists once per locale.
 */
#[Fillable(['key', 'locale', 'translation_of_id', 'title', 'content', 'body', 'meta_title', 'meta_description', 'focus_keyword', 'canonical_url', 'noindex'])]
class Page extends Model implements HasMedia
{
    use HasFactory, HasTranslations, RegistersImageConversions, ScoresSeo;

    public const KEYS = ['home', 'about', 'contact', 'blog', 'projects', 'not_found', 'privacy', 'terms'];

    /** Public path of each page (null = not indexable / no own URL). */
    public const PATHS = [
        'home' => '/',
        'about' => '/about',
        'contact' => '/contact',
        'blog' => '/blog',
        'projects' => '/projects',
        'not_found' => null,
        'privacy' => '/privacy',
        'terms' => '/terms',
    ];

    protected function casts(): array
    {
        return ['content' => 'array', 'body' => 'array', 'noindex' => 'boolean'];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('og_image')->useDisk('public')->singleFile();
    }

    public function path(): ?string
    {
        return self::PATHS[$this->key] ?? null;
    }

    public function seoInput(): SeoInput
    {
        if ($this->body) {
            return SeoInput::fromPage($this->title, $this->meta_title, $this->meta_description, $this->focus_keyword, $this->key, RichBody::normalize($this->body));
        }

        $strings = [];
        self::collectText($this->content ?? [], $strings);

        return SeoInput::fromPlainText($this->title, $this->meta_title, $this->meta_description, $this->focus_keyword, $this->key, $strings);
    }

    /** @param  list<string>  $out */
    private static function collectText(mixed $value, array &$out): void
    {
        if (is_string($value)) {
            $out[] = $value;
        } elseif (is_array($value) && ($value['type'] ?? null) === 'doc') {
            $out[] = RichBody::plain($value);
        } elseif (is_array($value)) {
            foreach ($value as $item) {
                self::collectText($item, $out);
            }
        }
    }

    public static function forKey(string $key, string $locale = 'en'): self
    {
        return static::query()->where('key', $key)->where('locale', $locale)->firstOrFail();
    }
}
