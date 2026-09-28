<?php

namespace Database\Factories;

use App\Models\ProcessStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProcessStep> */
class ProcessStepFactory extends Factory
{
    public function definition(): array
    {
        return [
            'icon' => 'chat',
            'title' => fake()->word(),
            'text' => fake()->sentence(12),
            'sort_order' => 0,
        ];
    }
}
