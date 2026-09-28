<?php

namespace Database\Factories;

use App\Enums\PublishStatus;
use App\Models\Project;
use App\Support\TipTapDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Project> */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->words(3, true),
            'summary' => fake()->sentence(12),
            'lead' => TipTapDocument::fromParagraphs([fake()->paragraph()]),
            'client' => fake()->company(),
            'role' => 'Full-stack developer',
            'timeline' => '8 weeks',
            'year' => 2026,
            'challenge' => TipTapDocument::fromParagraphs([fake()->paragraph()]),
            'solution' => TipTapDocument::fromParagraphs([fake()->paragraph()]),
            'result' => TipTapDocument::fromParagraphs([fake()->paragraph()]),
            'features' => [['icon' => 'grid', 'title' => fake()->words(3, true), 'text' => fake()->sentence()]],
            'stack' => ['Laravel', 'Next.js'],
            'metrics' => [['value' => '0.8s', 'label' => 'Average page load']],
            'cover_alt' => fake()->sentence(4),
            'featured' => false,
            'sort_order' => 0,
            'status' => PublishStatus::Published,
            'published_at' => now()->subDay(),
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => PublishStatus::Draft, 'published_at' => null]);
    }
}
