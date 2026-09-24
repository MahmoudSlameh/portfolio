<?php

namespace Database\Factories;

use App\Enums\ArchitectureNodeKind;
use App\Enums\ProjectCategory;
use App\Enums\ProjectLinkKind;
use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $titled = fn (): array => ['title' => fake()->sentence(3), 'description' => fake()->sentence(12)];

        return [
            'title' => fake()->unique()->words(2, true),
            'tagline' => fake()->sentence(8),
            'summary' => fake()->paragraph(),
            'year' => fake()->numberBetween(2016, 2026),
            'status' => fake()->randomElement(ProjectStatus::cases()),
            'category' => fake()->randomElement(ProjectCategory::cases()),
            'version' => 'v'.fake()->numberBetween(1, 4).'.0.0',
            'role' => 'Tech lead',
            'team' => '4 engineers, 1 designer',
            'timeline' => 'Mar 2024 — ongoing',
            'overview' => fake()->paragraphs(2),
            'problem' => fake()->paragraphs(2),
            'approach' => [$titled(), $titled()],
            'architecture' => [
                'caption' => fake()->sentence(),
                'columns' => 3,
                'rows' => 2,
                'nodes' => [
                    ['id' => 'web', 'label' => 'Web app', 'detail' => 'React', 'kind' => ArchitectureNodeKind::Client->value, 'column' => 1, 'row' => 1],
                    ['id' => 'api', 'label' => 'API', 'detail' => 'Laravel', 'kind' => ArchitectureNodeKind::Service->value, 'column' => 2, 'row' => 1],
                    ['id' => 'db', 'label' => 'Database', 'detail' => 'MySQL', 'kind' => ArchitectureNodeKind::Store->value, 'column' => 3, 'row' => 1],
                ],
                'edges' => [
                    ['from' => 'web', 'to' => 'api', 'label' => 'HTTPS'],
                    ['from' => 'api', 'to' => 'db'],
                ],
            ],
            'features' => [$titled(), $titled()],
            'challenges' => [$titled()],
            'metrics' => [['value' => '12 min', 'label' => 'close time', 'detail' => 'down from 9 hours']],
            'links' => [['label' => 'Live site', 'url' => fake()->url(), 'kind' => ProjectLinkKind::Live->value]],
            'cover_alt' => fake()->sentence(),
            'is_published' => true,
            'published_at' => now()->subDay(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (): array => ['is_published' => false, 'published_at' => null]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (): array => ['is_published' => true, 'published_at' => now()->addWeek()]);
    }

    public function featured(): static
    {
        return $this->state(fn (): array => ['is_featured' => true]);
    }
}
