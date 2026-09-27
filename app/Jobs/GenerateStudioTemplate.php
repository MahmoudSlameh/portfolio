<?php

namespace App\Jobs;

use App\Ai\Agents\TemplateDesigner;
use App\Ai\TemplateBrief;
use App\Enums\StudioStatus;
use App\Models\StudioGeneration;
use App\Models\StudioTemplate;
use App\Models\StudioTemplateVersion;
use App\Models\User;
use App\Support\Studio\AiSettings;
use App\Support\Studio\SpecValidator;
use App\Support\Templates\TemplateRegistry;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;
use Laravel\Ai\Files\Image;
use Laravel\Ai\Messages\AssistantMessage;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Messages\UserMessage;
use Laravel\Ai\Responses\AgentResponse;
use RuntimeException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

/**
 * Designs a studio template with AI in the background (docs/12-ai-templates.md §5): prompt, validate,
 * up to two repair turns, save the version. Progress is written to the template for the panel.
 */
class GenerateStudioTemplate implements ShouldQueue
{
    use Queueable;

    /** Repair turns after the first answer. */
    public const REPAIRS = 2;

    public int $tries = 1;

    public int $timeout = 300;

    public bool $failOnTimeout = true;

    public function __construct(public StudioGeneration $generation) {}

    public function handle(TemplateRegistry $registry): void
    {
        $template = $this->generation->template;

        if ($template === null) {
            return; // deleted while queued
        }

        $this->generation->update(['status' => StudioStatus::InProgress]);
        $this->step($template, 5, 'Starting…');

        try {
            $this->generate($template, $registry);
        } catch (Throwable $exception) {
            report($exception);
            $this->markFailed($template, $exception->getMessage());
        }
    }

    /**
     * Timeouts and worker crashes end here instead of in handle().
     */
    public function failed(?Throwable $exception): void
    {
        $template = $this->generation->template;

        if ($template !== null && $template->status->isWorking()) {
            $this->markFailed($template, $exception?->getMessage() ?? 'The generation stopped unexpectedly.');
        }
    }

    private function generate(StudioTemplate $template, TemplateRegistry $registry): void
    {
        $settings = AiSettings::current();

        if (! $settings->enabled()) {
            throw new RuntimeException($settings->problem ?? 'AI is not configured.');
        }

        $provider = $settings->register();

        $this->step($template, 15, 'Reading your references…');
        $images = $template->getMedia('reference')
            ->map(fn (Media $media): Image => Image::fromStorage($media->getPathRelativeToRoot(), $media->disk))
            ->values()
            ->all();

        $brief = $this->brief($template, $registry, count($images))->toPrompt();

        $this->step($template, 30, 'Designing the template…');
        $response = (new TemplateDesigner)->prompt($brief, attachments: $images, provider: $provider, model: $settings->model);
        $this->record($response);

        /** @var list<Message> $history */
        $history = [new UserMessage($brief, $images), new AssistantMessage($response->text)];
        $validator = new SpecValidator;

        $this->step($template, 70, 'Checking the design…');
        $json = TemplateDesigner::specFrom($response);
        $result = $validator->validateJson($json);

        for ($repair = 1; ! $result->passes() && $repair <= self::REPAIRS; $repair++) {
            $count = count($result->errors);
            $this->step($template, 70 + $repair * 10, "Fixing {$count} ".Str::plural('problem', $count)." (attempt {$repair} of ".self::REPAIRS.')…');

            $prompt = TemplateDesigner::repairPrompt($result->messages());
            $response = (new TemplateDesigner)->withMessages($history)->prompt($prompt, provider: $provider, model: $settings->model);
            $this->record($response);

            $history = [...$history, new UserMessage($prompt), new AssistantMessage($response->text)];
            $json = TemplateDesigner::specFrom($response);
            $result = $validator->validateJson($json);
        }

        if (! $result->passes()) {
            $messages = $result->messages();

            throw new RuntimeException(sprintf(
                'The design still had %d %s after %d fixes: %s',
                count($messages),
                Str::plural('problem', count($messages)),
                self::REPAIRS,
                implode('; ', array_map('trim', array_slice($messages, 0, 3))),
            ));
        }

        $this->step($template, 95, 'Saving…');

        /** @var array<string, mixed> $spec */
        $spec = json_decode($json, true);
        $spec['name'] = $template->name;
        $summary = TemplateDesigner::summaryFrom($response);

        $version = $template->addVersion($spec, [
            'prompt' => $this->generation->prompt,
            'parent' => $template->getRelationValue('activeVersion'),
            'provider' => $settings->provider,
            'model' => $response->meta->model ?? $settings->model,
            'input_tokens' => $this->generation->input_tokens,
            'output_tokens' => $this->generation->output_tokens,
        ]);
        $template->activate($version);

        if ($template->description === null && $summary !== '') {
            $template->update(['description' => Str::limit($summary, 250)]);
        }

        $this->generation->update(['status' => StudioStatus::Ready, 'model' => $version->model, 'error' => null]);
        $this->notify($template, $version, $summary);
    }

    private function brief(StudioTemplate $template, TemplateRegistry $registry, int $images): TemplateBrief
    {
        $startFrom = $this->generation->start_from;
        $startSpec = null;
        $startTemplate = null;

        if ($startFrom !== null && str_starts_with($startFrom, StudioTemplate::ID_PREFIX)) {
            $source = StudioTemplate::query()->with('activeVersion')->find(Str::after($startFrom, StudioTemplate::ID_PREFIX));
            $version = $source?->getRelationValue('activeVersion');
            $startSpec = $version instanceof StudioTemplateVersion ? $version->spec : null;
        } elseif ($startFrom !== null) {
            $startTemplate = $registry->find($startFrom);
        }

        return new TemplateBrief($template->name, (string) $this->generation->prompt, $startSpec, $startTemplate, $images);
    }

    private function record(AgentResponse $response): void
    {
        $this->generation->increment('turns');
        $this->generation->update([
            'input_tokens' => $this->generation->input_tokens + $response->usage->inputTokens,
            'output_tokens' => $this->generation->output_tokens + $response->usage->outputTokens,
            'model' => $response->meta->model ?? $this->generation->model,
        ]);
    }

    private function step(StudioTemplate $template, int $progress, string $step): void
    {
        $template->forceFill(['status' => StudioStatus::InProgress, 'progress' => $progress, 'current_step' => $step])->save();
    }

    private function markFailed(StudioTemplate $template, string $message): void
    {
        $message = Str::limit($this->redact($message), 500);

        $template->forceFill([
            'status' => StudioStatus::Failed,
            'current_step' => null,
            'error' => $message,
        ])->save();

        $this->generation->update(['status' => StudioStatus::Failed, 'error' => $message]);

        Notification::make()
            ->danger()
            ->title("{$template->name} could not be generated")
            ->body($message)
            ->actions([Action::make('open')->label('Open Appearance')->url(url('/admin/appearance'))])
            ->sendToDatabase(User::all());
    }

    private function notify(StudioTemplate $template, StudioTemplateVersion $version, string $summary): void
    {
        Notification::make()
            ->success()
            ->title("{$template->name} is ready")
            ->body($summary !== '' ? $summary : "Version {$version->number} was generated. Preview it before activating.")
            ->actions([Action::make('preview')->label('Preview')->url(url('/?template='.$template->templateId()), shouldOpenInNewTab: true)])
            ->sendToDatabase(User::all());
    }

    /**
     * Provider errors sometimes echo the request; never store the key.
     */
    private function redact(string $message): string
    {
        $key = config('ai.providers.'.AiSettings::PROVIDER_NAME.'.key');

        return is_string($key) && $key !== '' ? str_replace($key, '[key]', $message) : $message;
    }
}
