<?php

namespace Database\Factories;

use App\Models\Skill;
use App\Models\SkillCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Skill>
 */
class SkillFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word().' '.fake()->unique()->numberBetween(1, 99999),
            'skill_category_id' => SkillCategory::factory(),
            'proficiency' => fake()->numberBetween(1, 5),
            'years' => fake()->numberBetween(1, 12),
        ];
    }

    /**
     * A technology used only as a stack tag (not listed in the skills section).
     */
    public function stackOnly(): static
    {
        return $this->state(fn (): array => ['skill_category_id' => null, 'proficiency' => null, 'years' => null]);
    }
}
