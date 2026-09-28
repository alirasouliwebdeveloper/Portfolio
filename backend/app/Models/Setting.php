<?php

namespace App\Models;

use App\Models\Concerns\RegistersImageConversions;
use App\Models\Concerns\ScoresSeo;
use App\Support\Seo\SeoInput;
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
    use HasFactory, RegistersImageConversions, ScoresSeo;

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
        $this->addMediaCollection('portrait')->singleFile();
        $this->addMediaCollection('cv')->singleFile();
        $this->addMediaCollection('og_image')->singleFile();
        $this->addMediaCollection('logo')->singleFile();
        $this->addMediaCollection('logo_admin')->singleFile();
        $this->addMediaCollection('favicon')->singleFile();
    }

    public function seoInput(): SeoInput
    {
        return SeoInput::fromPlainText(
            trim("Hi, I'm {$this->name}. {$this->headline}"),
            $this->meta_title,
            $this->meta_description,
            $this->focus_keyword,
            '',
            [(string) $this->bio_short],
        );
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
