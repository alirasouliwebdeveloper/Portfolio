<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['icon', 'title', 'locale', 'translation_of_id', 'text', 'sort_order'])]
class ProcessStep extends Model
{
    use HasFactory, HasTranslations;
}
