<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectGalleryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectGalleryItem>
 */
class ProjectGalleryItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'alt' => fake()->sentence(),
            'caption' => fake()->sentence(),
        ];
    }
}
