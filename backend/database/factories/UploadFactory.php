<?php

namespace Database\Factories;

use App\Models\Upload;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Upload> */
class UploadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'upload_session' => (string) Str::uuid(),
            'original_name' => 'brief.pdf',
            'mime' => 'application/pdf',
            'size' => fake()->numberBetween(10_000, 5_000_000),
            'path' => 'uploads/'.Str::random(40).'.pdf',
            'contact_message_id' => null,
            'expires_at' => now()->addDay(),
        ];
    }
}
