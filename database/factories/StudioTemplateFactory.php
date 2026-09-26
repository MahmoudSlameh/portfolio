<?php

namespace Database\Factories;

use App\Enums\StudioSource;
use App\Enums\StudioStatus;
use App\Models\StudioTemplate;
use App\Support\Studio\SpecCatalogue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudioTemplate>
 */
class StudioTemplateFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ucwords(fake()->word().' '.fake()->word()),
            'description' => fake()->sentence(),
            'source' => StudioSource::Manual,
            'status' => StudioStatus::Draft,
        ];
    }

    /**
     * Ready to render: one active version with the example spec (or the given one).
     *
     * @param  array<string, mixed>|null  $spec
     */
    public function ready(?array $spec = null): static
    {
        return $this->afterCreating(fn (StudioTemplate $template) => $template->addVersion($spec ?? SpecCatalogue::example()));
    }

    public function failed(string $error = 'The provider returned an invalid spec.'): static
    {
        return $this->state(['status' => StudioStatus::Failed, 'error' => $error, 'source' => StudioSource::Ai]);
    }

    public function generating(int $progress = 45): static
    {
        return $this->state(['status' => StudioStatus::InProgress, 'progress' => $progress, 'current_step' => 'Composing pages…', 'source' => StudioSource::Ai]);
    }
}
