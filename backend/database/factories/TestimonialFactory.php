<?php

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Testimonial> */
class TestimonialFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->name();

        return [
            'quote' => fake()->paragraph(),
            'name' => $name,
            'role' => fake()->jobTitle(),
            'company' => fake()->company(),
            'initials' => collect(explode(' ', $name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode(''),
            'rating' => 5,
            'featured' => true,
            'sort_order' => 0,
        ];
    }
}
