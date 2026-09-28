<?php

namespace Database\Factories;

use App\Enums\PublishStatus;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Service> */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true).' development';

        return [
            'title' => $title,
            'nav_label' => $title,
            'h1' => fake()->sentence(8),
            'lead' => fake()->paragraph(),
            'icon' => 'code',
            'hero_image_alt' => fake()->sentence(4),
            'floating_metric' => ['value' => '0.8s', 'label' => 'Average response'],
            'pains' => [['icon' => 'alert', 'title' => fake()->sentence(4), 'text' => fake()->sentence()]],
            'offers' => [['icon' => 'code', 'title' => fake()->sentence(3), 'text' => fake()->sentence()]],
            'why' => ['title' => 'Why this stack?', 'text' => fake()->paragraph(), 'points' => [fake()->sentence(4)]],
            'stack' => ['Laravel', 'MySQL'],
            'tiers' => [['name' => 'Starter', 'price_from' => '$1,500', 'subtitle' => fake()->sentence(4), 'items' => [fake()->sentence(3)], 'highlighted' => false]],
            'faq' => [['question' => fake()->sentence(6).'?', 'answer' => fake()->paragraph()]],
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
