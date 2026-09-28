<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use App\Models\Concerns\RegistersImageConversions;
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
    'meta_title', 'meta_description',
])]
class Project extends Model implements HasMedia
{
    use HasFactory, HasSlug, Publishable, RegistersImageConversions;

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'stack' => 'array',
            'metrics' => 'array',
            'featured' => 'boolean',
            'year' => 'integer',
        ];
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
