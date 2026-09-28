<?php

namespace App\Models;

use App\Enums\ContactMessageStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 'email', 'company', 'phone', 'need', 'budget', 'timeline', 'message',
    'service_slug', 'ip_hash', 'user_agent', 'status',
])]
class ContactMessage extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['status' => ContactMessageStatus::class];
    }

    public function uploads(): HasMany
    {
        return $this->hasMany(Upload::class);
    }
}
