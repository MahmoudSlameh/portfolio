<?php

namespace Database\Factories;

use App\Enums\UsesKind;
use App\Models\UsesGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UsesGroup>
 */
class UsesGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kind' => fake()->randomElement(UsesKind::cases()),
            'title' => fake()->word(),
        ];
    }
}
