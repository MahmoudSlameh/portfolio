<?php

namespace Database\Factories;

use App\Enums\CompanyKind;
use App\Enums\WordmarkStyle;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => str($name)->slug()->toString(),
            'kind' => CompanyKind::Employer,
            'website_url' => fake()->url(),
            'industry' => fake()->randomElement(['Fintech', 'Logistics', 'E-commerce', 'Healthcare', 'SaaS']),
            'city' => fake()->city(),
            'country_code' => fake()->countryCode(),
            'engagement' => fake()->sentence(),
            'wordmark_style' => fake()->randomElement(WordmarkStyle::cases()),
        ];
    }

    public function client(): static
    {
        return $this->state(fn (): array => ['kind' => CompanyKind::Client]);
    }

    public function hidden(): static
    {
        return $this->state(fn (): array => ['is_visible' => false]);
    }
}
