<?php

namespace Database\Factories;

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Post;
use App\Support\TipTapDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Post> */
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'title' => fake()->unique()->sentence(6),
            'excerpt' => fake()->sentence(18),
            'body' => TipTapDocument::fromParagraphs([fake()->paragraph(6)]),
            'featured' => false,
            'cover_alt' => fake()->sentence(4),
            'status' => PublishStatus::Published,
            'published_at' => now()->subDay(),
        ];
    }

    public function draft(): static
    {
        return $this->state(['status' => PublishStatus::Draft, 'published_at' => null]);
    }

    public function scheduled(): static
    {
        return $this->state(['status' => PublishStatus::Published, 'published_at' => now()->addDay()]);
    }

    public function featured(): static
    {
        return $this->state(['featured' => true]);
    }
}
