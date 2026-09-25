<?php

namespace Database\Factories;

use App\Models\NowPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NowPage>
 */
class NowPageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'location' => fake()->city(),
            'availability' => fake()->sentence(),
            'focus' => [['title' => fake()->sentence(3), 'body' => fake()->sentence()]],
            'learning' => [['title' => fake()->word(), 'body' => fake()->sentence()]],
        ];
    }
}
