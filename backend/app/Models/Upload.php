<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'uuid', 'upload_session', 'original_name', 'mime', 'size', 'path', 'contact_message_id', 'expires_at',
])]
class Upload extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['size' => 'integer', 'expires_at' => 'datetime'];
    }

    public function contactMessage(): BelongsTo
    {
        return $this->belongsTo(ContactMessage::class);
    }
}
