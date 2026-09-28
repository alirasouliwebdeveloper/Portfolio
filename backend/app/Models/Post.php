<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use App\Models\Concerns\RegistersImageConversions;
use App\Models\Concerns\ScoresSeo;
use App\Support\RichBody;
use App\Support\Seo\SeoInput;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

#[Fillable([
    'category_id', 'title', 'slug', 'excerpt', 'body', 'featured', 'reading_time', 'cover_alt',
    'related_service_id', 'status', 'published_at', 'meta_title', 'meta_description',
    'focus_keyword', 'canonical_url', 'noindex', 'search_text',
])]
class Post extends Model implements HasMedia
{
    use HasFactory, HasSlug, Publishable, RegistersImageConversions, ScoresSeo;

    public const WORDS_PER_MINUTE = 220;

    protected function casts(): array
    {
        return ['featured' => 'boolean', 'reading_time' => 'integer', 'body' => 'array', 'noindex' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (Post $post): void {
            if ($post->isDirty('body')) {
                $post->reading_time = self::readingTimeFor($post->body);
                $post->search_text = RichBody::plain($post->body);
            }
        });
    }

    public static function readingTimeFor(mixed $body): int
    {
        $words = str_word_count(RichBody::plain($body));

        return max(1, (int) ceil($words / self::WORDS_PER_MINUTE));
    }

    /** Published, and its category is published too (otherwise the category link would 404). */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->published()->whereHas('category', fn (Builder $category) => $category->published());
    }

    public function seoInput(): SeoInput
    {
        return SeoInput::fromPage(
            $this->title,
            $this->meta_title,
            $this->meta_description,
            $this->focus_keyword,
            $this->slug,
            RichBody::normalize($this->body),
            $this->excerpt,
        );
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('og_image')->singleFile();
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->preventOverwrite()
            ->doNotGenerateSlugsOnUpdate();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function relatedService(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'related_service_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_post')->withPivot('sort_order');
    }
}
