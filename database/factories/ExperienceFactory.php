<?php

namespace Database\Factories;

use App\Enums\EmploymentType;
use App\Enums\WorkMode;
use App\Models\Company;
use App\Models\Experience;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Experience>
 */
class ExperienceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-10 years', '-2 years');

        return [
            'company_id' => Company::factory(),
            'role' => fake()->jobTitle(),
            'employment_type' => EmploymentType::FullTime,
            'work_mode' => fake()->randomElement(WorkMode::cases()),
            'country_code' => fake()->countryCode(),
            'city' => fake()->city(),
            'start_date' => $start->format('Y-m-01'),
            'end_date' => fake()->dateTimeBetween($start, 'now')->format('Y-m-01'),
            'summary' => fake()->sentence(15),
            'highlights' => [fake()->sentence(), fake()->sentence()],
        ];
    }

    public function current(): static
    {
        return $this->state(fn (): array => ['end_date' => null]);
    }

    public function remote(): static
    {
        return $this->state(fn (): array => ['work_mode' => WorkMode::Remote]);
    }

    public function openSource(): static
    {
        return $this->state(fn (): array => [
            'company_id' => null,
            'organization_name' => fake()->word().' (open source)',
            'employment_type' => EmploymentType::OpenSource,
        ]);
    }
}
