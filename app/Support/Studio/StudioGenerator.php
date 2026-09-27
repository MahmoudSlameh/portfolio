<?php

namespace App\Support\Studio;

use App\Enums\StudioSource;
use App\Enums\StudioStatus;
use App\Jobs\GenerateStudioTemplate;
use App\Models\StudioGeneration;
use App\Models\StudioTemplate;

/**
 * Starts AI generations (docs/12-ai-templates.md §5): checks the settings and the daily limit,
 * records the attempt and queues the job.
 */
final class StudioGenerator
{
    /**
     * @param  string|null  $startFrom  A template id (`studio:<ulid>` or a code template id) to start from
     *
     * @throws GenerationRefused
     */
    public function start(StudioTemplate $template, string $prompt, ?string $startFrom = null): StudioGeneration
    {
        $settings = AiSettings::current();

        if (! $settings->enabled()) {
            throw new GenerationRefused($settings->problem ?? 'AI is not configured.');
        }

        if ($this->remainingToday() <= 0) {
            throw new GenerationRefused("Today's limit of {$settings->dailyLimit} generations is reached. Raise it in Site → AI or try again tomorrow.");
        }

        $generation = $template->generations()->create([
            'prompt' => $prompt,
            'start_from' => $startFrom,
            'provider' => $settings->provider,
            'model' => $settings->model,
        ]);

        $template->forceFill([
            'source' => StudioSource::Ai,
            'status' => StudioStatus::Queued,
            'progress' => 0,
            'current_step' => 'Waiting for a worker…',
            'error' => null,
        ])->save();

        GenerateStudioTemplate::dispatch($generation);

        return $generation;
    }

    public function remainingToday(): int
    {
        return max(0, AiSettings::current()->dailyLimit - StudioGeneration::query()->today()->count());
    }
}
