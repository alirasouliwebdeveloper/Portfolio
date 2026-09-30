<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\RegistersImageConversions;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;

#[Fillable(['quote', 'name', 'locale', 'translation_of_id', 'role', 'company', 'initials', 'rating', 'featured', 'sort_order'])]
class Testimonial extends Model implements HasMedia
{
    use HasFactory, HasTranslations, RegistersImageConversions;

    protected function casts(): array
    {
        return ['featured' => 'boolean', 'rating' => 'integer'];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->useDisk('public')->singleFile();
    }
}
