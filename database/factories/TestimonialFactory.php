<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quote' => fake()->paragraph(),
            'author_name' => fake()->name(),
            'author_role' => fake()->jobTitle(),
            'company_id' => Company::factory(),
            'relation' => 'Worked together',
        ];
    }
}
