<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['icon', 'title', 'text', 'sort_order'])]
class ProcessStep extends Model
{
    use HasFactory;
}
