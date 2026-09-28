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
    'meta_title', 'meta_description',
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
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('portrait')->singleFile();
        $this->addMediaCollection('cv')->singleFile();
        $this->addMediaCollection('og_image')->singleFile();
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
