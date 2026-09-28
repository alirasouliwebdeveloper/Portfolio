<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Old public path -> current path, created automatically when a slug changes. */
class Redirect extends Model
{
    protected $fillable = ['from_path', 'to_path', 'status_code'];
}
