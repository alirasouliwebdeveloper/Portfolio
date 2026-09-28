<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['role', 'company', 'start_year', 'end_year', 'description', 'sort_order'])]
class Experience extends Model
{
    use HasFactory;
}
