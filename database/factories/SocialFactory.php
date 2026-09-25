<?php

namespace Database\Factories;

use App\Enums\SocialPlatform;
use App\Models\Social;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Social>
 */
class SocialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $platform = fake()->randomElement(SocialPlatform::cases());

        return [
            'platform' => $platform,
            'label' => $platform->getLabel(),
            'handle' => '@'.fake()->userName(),
            'url' => fake()->url(),
        ];
    }
}
