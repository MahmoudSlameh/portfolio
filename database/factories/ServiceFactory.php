<?php

namespace Database\Factories;

use App\Enums\ServiceIcon;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->words(3, true),
            'summary' => fake()->sentence(18),
            'icon' => fake()->randomElement(ServiceIcon::cases()),
            'highlights' => fake()->words(3),
        ];
    }

    public function hidden(): static
    {
        return $this->state(['is_visible' => false]);
    }
}
