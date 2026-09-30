<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['question', 'answer', 'locale', 'translation_of_id', 'scope', 'sort_order'])]
class Faq extends Model
{
    use HasFactory, HasTranslations;

    protected function casts(): array
    {
        return ['answer' => 'array'];
    }
}
