<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use App\Models\Concerns\RegistersImageConversions;
use App\Models\Concerns\ScoresSeo;
use App\Support\RichBody;
use App\Support\Seo\SeoInput;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

#[Fillable([
    'slug', 'nav_label', 'title', 'h1', 'lead', 'icon', 'hero_image_alt', 'floating_metric',
    'pains', 'offers', 'why', 'stack', 'tiers', 'faq', 'related_project_id', 'related_category_id',
    'sort_order', 'status', 'published_at', 'meta_title', 'meta_description',
    'focus_keyword', 'canonical_url', 'noindex',
])]
class Service extends Model implements HasMedia
{
    use HasFactory, HasSlug, Publishable, RegistersImageConversions, ScoresSeo;

    protected function casts(): array
    {
        return [
            'floating_metric' => 'array',
            'pains' => 'array',
            'offers' => 'array',
            'why' => 'array',
            'stack' => 'array',
            'tiers' => 'array',
            'faq' => 'array',
            'noindex' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('hero_image')->singleFile();
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

    public function seoInput(): SeoInput
    {
        return SeoInput::fromPlainText(
            $this->h1,
            $this->meta_title,
            $this->meta_description,
            $this->focus_keyword,
            $this->slug,
            [$this->lead, RichBody::plain($this->why['text'] ?? null), ...collect($this->offers)->pluck('text')->all()],
        );
    }

    public function relatedProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'related_project_id');
    }

    public function relatedCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'related_category_id');
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'service_post')->withPivot('sort_order')->orderByPivot('sort_order');
    }
}
