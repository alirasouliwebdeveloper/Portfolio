<?php

namespace Database\Factories;

use App\Enums\ContactMessageStatus;
use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ContactMessage> */
class ContactMessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'company' => fake()->company(),
            'phone' => fake()->phoneNumber(),
            'need' => 'Online store',
            'budget' => '$4,000 – $8,000',
            'timeline' => 'Within 1–3 months',
            'message' => fake()->paragraph(),
            'service_slug' => null,
            'ip_hash' => hash('sha256', fake()->ipv4()),
            'user_agent' => fake()->userAgent(),
            'status' => ContactMessageStatus::New,
        ];
    }
}
