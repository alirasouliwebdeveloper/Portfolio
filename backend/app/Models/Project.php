<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use App\Models\Concerns\RegistersImageConversions;
use App\Models\Concerns\ScoresSeo;
use App\Models\Concerns\TracksSlugRedirects;
use App\Support\RichBody;
use App\Support\Seo\SeoInput;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

#[Fillable([
    'title', 'slug', 'summary', 'lead', 'client', 'role', 'timeline', 'year', 'live_url',
    'challenge', 'solution', 'result', 'features', 'stack', 'metrics', 'cover_alt',
    'testimonial_id', 'service_id', 'featured', 'sort_order', 'status', 'published_at',
    'meta_title', 'meta_description', 'focus_keyword', 'canonical_url', 'noindex',
])]
class Project extends Model implements HasMedia
{
    use HasFactory, HasSlug, Publishable, RegistersImageConversions, ScoresSeo, TracksSlugRedirects;

    /** Public frontend path for a slug (used to keep old URLs redirecting). */
    public function publicPath(string $slug): string
    {
        return '/projects/'.$slug;
    }

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'stack' => 'array',
            'metrics' => 'array',
            'featured' => 'boolean',
            'year' => 'integer',
            'noindex' => 'boolean',
            'lead' => 'array',
            'challenge' => 'array',
            'solution' => 'array',
            'result' => 'array',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->useDisk('public')->singleFile();
        $this->addMediaCollection('og_image')->useDisk('public')->singleFile();
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->preventOverwrite()
            ->doNotGenerateSlugsOnUpdate();
    }

    public function seoInput(): SeoInput
    {
        return SeoInput::fromPlainText(
            $this->title,
            $this->meta_title,
            $this->meta_description,
            $this->focus_keyword,
            $this->slug,
            [$this->summary, RichBody::plain($this->lead), RichBody::plain($this->challenge), RichBody::plain($this->solution), RichBody::plain($this->result)],
        );
    }

    public function testimonial(): BelongsTo
    {
        return $this->belongsTo(Testimonial::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function screens(): HasMany
    {
        return $this->hasMany(ProjectScreen::class)->orderBy('sort_order');
    }
}
