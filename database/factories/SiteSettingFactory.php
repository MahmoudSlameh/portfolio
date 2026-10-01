<?php

namespace Database\Factories;

use App\Models\SiteSetting;
use App\Support\Templates\TemplateRegistry;
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
            'active_template' => fake()->randomElement(app(TemplateRegistry::class)->ids()),
            'site_name' => fake()->name(),
            'meta_description' => fake()->sentence(),
            'indexable' => true,
        ];
    }
}
