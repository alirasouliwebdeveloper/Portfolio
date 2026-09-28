<?php

namespace Database\Factories;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Faq> */
class FaqFactory extends Factory
{
    public function definition(): array
    {
        return [
            'question' => fake()->sentence(6).'?',
            'answer' => fake()->paragraph(),
            'scope' => 'contact',
            'sort_order' => 0,
        ];
    }
}
