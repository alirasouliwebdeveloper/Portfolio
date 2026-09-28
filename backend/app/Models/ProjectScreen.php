<?php

namespace App\Models;

use App\Models\Concerns\RegistersImageConversions;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;

#[Fillable(['project_id', 'caption', 'alt', 'sort_order'])]
class ProjectScreen extends Model implements HasMedia
{
    use HasFactory, RegistersImageConversions;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
