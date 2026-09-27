<?php

namespace App\Console\Commands;

use App\Models\StudioTemplate;
use App\Support\Templates\TemplateEjector;
use App\Support\Templates\TemplateRegistry;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use InvalidArgumentException;

#[Signature('template:eject
    {template : The studio template: its id (studio:<ulid> or the ulid) or its exact name}
    {id : Lowercase slug of the new code template, e.g. "night-shift"}
    {--name= : Display name (defaults to the studio template\'s name)}
    {--author= : Author shown in the manifest}
    {--no-format : Skip formatting the generated files with Vite+}')]
#[Description('Turn a studio template into a code template you can edit in TSX')]
class TemplateEjectCommand extends Command
{
    public function handle(TemplateEjector $ejector, TemplateRegistry $registry): int
    {
        $template = $this->find((string) $this->argument('template'));

        if ($template === null) {
            $this->components->error('No studio template matches "'.$this->argument('template').'". Studio templates: '.StudioTemplate::query()->pluck('name')->implode(', '));

            return self::FAILURE;
        }

        $id = (string) $this->argument('id');

        try {
            $paths = $ejector->eject($template, $id, $this->option('name') ?: null, $this->option('author') ?: null);
        } catch (InvalidArgumentException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $registry->refresh();

        foreach (['created' => 'green', 'updated' => 'yellow'] as $status => $color) {
            foreach ($paths[$status] as $path) {
                $this->components->twoColumnDetail(str_replace(base_path().'/', '', $path), "<fg={$color}>{$status}</>");
            }
        }

        if (! $this->option('no-format')) {
            $result = Process::path(base_path())->timeout(120)->run(['npx', 'vp', 'fmt', ...$paths['created'], ...$paths['updated']]);

            if ($result->failed()) {
                $this->components->warn('Could not format the generated files; run `npm run check:fix`.');
            }
        }

        $this->components->info("Template [{$id}] ejected from the studio template [{$template->name}].");
        $this->components->bulletList([
            'Run `composer dev` (or `npm run build:ssr` for SSR) so Vite picks up the new pages.',
            "Preview it at /?template={$id} while signed in to the panel, then activate it on Appearance.",
            "The design is in resources/js/templates/{$id}/frozenSpec.ts; the markup is yours to change.",
            'Run `composer ci:check`: every page of the new template is tested automatically.',
        ]);

        return self::SUCCESS;
    }

    private function find(string $reference): ?StudioTemplate
    {
        $ulid = Str::after($reference, StudioTemplate::ID_PREFIX);

        return StudioTemplate::query()->find($ulid)
            ?? StudioTemplate::query()->where('name', $reference)->first();
    }
}
