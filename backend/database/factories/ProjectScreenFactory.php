<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectScreen;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProjectScreen> */
class ProjectScreenFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'caption' => fake()->words(3, true),
            'alt' => fake()->sentence(4),
            'sort_order' => 0,
        ];
    }
}
