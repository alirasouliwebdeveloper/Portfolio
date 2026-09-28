<?php

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Setting> */
class SettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Ali',
            'headline' => 'I build things for the web.',
            'bio_short' => fake()->sentence(14),
            'email' => fake()->safeEmail(),
            'phone' => '+968 9123 4567',
            'whatsapp' => '+968 9123 4567',
            'city' => 'Muscat, Oman',
            'working_hours' => 'Sun–Thu, 9:00–18:00',
            'response_time' => 'Usually within one working day',
            'socials' => ['github' => 'https://github.com/example', 'linkedin' => 'https://www.linkedin.com/in/example'],
            'stats' => [['icon' => 'calendar', 'value' => '4+', 'label' => 'Years Experience']],
            'popular_searches' => ['Laravel', 'Docker'],
            'contact_options' => ['needs' => ['Website or web app'], 'budgets' => ['Not sure yet'], 'timelines' => ['Flexible']],
            'portrait_alt' => 'Portrait of Ali',
        ];
    }
}
