<?php

namespace Database\Factories;

use App\Enums\ArticleStatus;
use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(5),
            'excerpt' => fake()->sentence(20),
            'body' => [
                ['type' => 'paragraph', 'data' => ['text' => fake()->paragraph()]],
                ['type' => 'heading', 'data' => ['text' => 'Start with invariants', 'id' => 'start-with-invariants']],
                ['type' => 'code', 'data' => ['language' => 'ts', 'filename' => 'journal.ts', 'code' => 'const total = 0;']],
                ['type' => 'list', 'data' => ['items' => [fake()->sentence(), fake()->sentence()]]],
                ['type' => 'callout', 'data' => ['title' => 'Note', 'text' => fake()->sentence()]],
                ['type' => 'quote', 'data' => ['text' => fake()->sentence(), 'cite' => fake()->name()]],
            ],
            'tags' => fake()->randomElements(['architecture', 'payments', 'postgres', 'react', 'laravel'], 2),
            'status' => ArticleStatus::Published,
            'published_at' => fake()->dateTimeBetween('-2 years', '-1 day'),
            'cover_alt' => fake()->sentence(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (): array => ['status' => ArticleStatus::Draft, 'published_at' => null]);
    }
}
