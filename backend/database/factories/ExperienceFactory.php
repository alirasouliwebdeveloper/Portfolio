<?php

namespace Database\Factories;

use App\Models\Experience;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Experience> */
class ExperienceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'role' => fake()->jobTitle(),
            'company' => fake()->company(),
            'start_year' => 2022,
            'end_year' => null,
            'description' => fake()->sentence(14),
            'sort_order' => 0,
        ];
    }
}
