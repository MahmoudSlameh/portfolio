<?php

namespace Database\Factories;

use App\Enums\BookCategory;
use App\Enums\BookCoverStyle;
use App\Enums\ReadingStatus;
use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(3),
            'author' => fake()->name(),
            'published_year' => fake()->numberBetween(1950, 2025),
            'category' => fake()->randomElement(BookCategory::cases()),
            'status' => ReadingStatus::Read,
            'finished_at' => fake()->dateTimeBetween('-4 years', 'now')->format('Y-m-01'),
            'rating' => fake()->numberBetween(1, 5),
            'pages' => fake()->numberBetween(120, 700),
            'note' => fake()->sentence(),
            'cover_background' => fake()->hexColor(),
            'cover_ink' => fake()->hexColor(),
            'cover_accent' => fake()->hexColor(),
            'cover_style' => fake()->randomElement(BookCoverStyle::cases()),
        ];
    }

    public function reading(): static
    {
        return $this->state(fn (): array => ['status' => ReadingStatus::Reading, 'finished_at' => null, 'rating' => null]);
    }

    public function toRead(): static
    {
        return $this->state(fn (): array => ['status' => ReadingStatus::ToRead, 'finished_at' => null, 'rating' => null]);
    }
}
