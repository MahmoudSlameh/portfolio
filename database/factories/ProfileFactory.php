<?php

namespace Database\Factories;

use App\Enums\AvailabilityStatus;
use App\Enums\StatusTone;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'role' => fake()->jobTitle(),
            'headline' => fake()->sentence(12),
            'focus_areas' => fake()->words(4),
            'summary' => fake()->paragraph(),
            'story' => fake()->paragraphs(3),
            'location' => fake()->city().', '.fake()->country(),
            'timezone' => 'Europe/Berlin',
            'timezone_label' => 'CET · UTC+1/+2',
            'email' => fake()->safeEmail(),
            'current_version' => 'v1.0.0',
            'availability_status' => fake()->randomElement(AvailabilityStatus::cases()),
            'availability_label' => 'Open to new roles',
            'availability_note' => fake()->sentence(),
            'latest_release' => ['added' => [fake()->sentence(4)], 'changed' => [], 'removed' => []],
            'stats' => [['value' => '8', 'label' => 'years shipping software']],
            'status' => [['label' => 'Building', 'value' => fake()->words(3, true), 'tone' => StatusTone::Signal->value]],
            'principles' => [['title' => fake()->sentence(3), 'body' => fake()->sentence()]],
            'portrait_alt' => 'Portrait photo',
        ];
    }
}
