<?php

namespace Database\Factories;

use App\Models\Certification;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Certification>
 */
class CertificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(4),
            'issuer' => fake()->company(),
            'issued_at' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'credential_id' => strtoupper(fake()->bothify('???-#####')),
            'credential_url' => fake()->url(),
        ];
    }
}
