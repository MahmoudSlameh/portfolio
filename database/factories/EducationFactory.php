<?php

namespace Database\Factories;

use App\Models\Education;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Education>
 */
class EducationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-12 years', '-4 years');

        return [
            'degree' => fake()->randomElement(['BSc', 'MSc', 'Diploma in Software Engineering']),
            'institution' => fake()->company().' University',
            'field_of_study' => fake()->randomElement(['Computer Science', 'Software Engineering', 'Information Systems']),
            'grade' => fake()->randomElement(['Distinction', 'Very good', '3.6 / 4.0']),
            'country_code' => fake()->countryCode(),
            'city' => fake()->city(),
            'start_date' => $start->format('Y-m-01'),
            'end_date' => fake()->dateTimeBetween($start, '-1 year')->format('Y-m-01'),
            'achievements' => [fake()->sentence()],
        ];
    }

    public function current(): static
    {
        return $this->state(fn (): array => ['end_date' => null]);
    }
}
