<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['question', 'answer', 'scope', 'sort_order'])]
class Faq extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['answer' => 'array'];
    }
}
