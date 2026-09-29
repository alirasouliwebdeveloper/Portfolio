<?php

namespace App\Models;

use App\Models\Concerns\RegistersImageConversions;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;

#[Fillable([
    'name', 'headline', 'bio_short', 'email', 'phone', 'whatsapp', 'city', 'working_hours',
    'response_time', 'socials', 'stats', 'popular_searches', 'contact_options', 'portrait_alt',
    'meta_title', 'meta_description', 'brand_name', 'tagline', 'footer_text', 'ga_measurement_id',
    'gsc_verification', 'twitter_handle', 'site_noindex', 'focus_keyword',
])]
class Setting extends Model implements HasMedia
{
    use HasFactory, RegistersImageConversions;

    protected function casts(): array
    {
        return [
            'socials' => 'array',
            'stats' => 'array',
            'popular_searches' => 'array',
            'contact_options' => 'array',
            'site_noindex' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('portrait')->useDisk('public')->singleFile();
        $this->addMediaCollection('cv')->useDisk('public')->singleFile();
        $this->addMediaCollection('og_image')->useDisk('public')->singleFile();
        $this->addMediaCollection('logo')->useDisk('public')->singleFile();
        $this->addMediaCollection('logo_admin')->useDisk('public')->singleFile();
        $this->addMediaCollection('favicon')->useDisk('public')->singleFile();
    }

    /** Persists the single settings record on first use (fresh database). */
    public static function ensure(): self
    {
        return static::query()->first() ?? static::create([
            'name' => 'Ali',
            'headline' => 'I build things for the web.',
            'email' => 'hello@example.com',
        ]);
    }

    /** The site has exactly one settings record. */
    public static function current(): self
    {
        return static::query()->first() ?? new static([
            'name' => 'Ali',
            'headline' => '',
            'email' => '',
        ]);
    }
}
