<?php

namespace Database\Factories;

use App\Models\UsesGroup;
use App\Models\UsesItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UsesItem>
 */
class UsesItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uses_group_id' => UsesGroup::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'url' => fake()->url(),
        ];
    }
}
