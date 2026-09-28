<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use App\Models\Concerns\RegistersImageConversions;
use App\Models\Concerns\ScoresSeo;
use App\Models\Concerns\TracksSlugRedirects;
use App\Support\Seo\SeoInput;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

#[Fillable(['name', 'slug', 'description', 'sort_order', 'status', 'published_at', 'meta_title', 'meta_description', 'focus_keyword', 'canonical_url', 'noindex'])]
class Category extends Model implements HasMedia
{
    use HasFactory, HasSlug, Publishable, RegistersImageConversions, ScoresSeo, TracksSlugRedirects;

    /** Public frontend path for a slug (used to keep old URLs redirecting). */
    public function publicPath(string $slug): string
    {
        return '/blog/category/'.$slug;
    }

    protected function casts(): array
    {
        return ['noindex' => 'boolean'];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('og_image')->singleFile();
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->preventOverwrite()
            ->doNotGenerateSlugsOnUpdate();
    }

    public function seoInput(): SeoInput
    {
        return SeoInput::fromPlainText(
            $this->name,
            $this->meta_title,
            $this->meta_description,
            $this->focus_keyword,
            $this->slug,
            [(string) $this->description],
        );
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
