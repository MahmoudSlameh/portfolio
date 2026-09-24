<?php

namespace Database\Factories;

use App\Enums\Template;
use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteSetting>
 */
class SiteSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'active_template' => fake()->randomElement(Template::cases()),
            'site_name' => fake()->name(),
            'meta_description' => fake()->sentence(),
            'indexable' => true,
        ];
    }
}
