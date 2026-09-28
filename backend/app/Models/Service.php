<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use App\Models\Concerns\RegistersImageConversions;
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
])]
class Service extends Model implements HasMedia
{
    use HasFactory, HasSlug, Publishable, RegistersImageConversions;

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
