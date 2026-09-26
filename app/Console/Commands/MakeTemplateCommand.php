<?php

namespace App\Console\Commands;

use App\Support\Templates\TemplateRegistry;
use App\Support\Templates\TemplateScaffolder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use InvalidArgumentException;

#[Signature('make:template
    {id : Lowercase slug of the new template, e.g. "magazine"}
    {--from=minimal : Template to copy}
    {--name= : Display name (defaults to the id in title case)}
    {--description= : Short description shown on the Appearance page}
    {--author= : Author shown in the manifest}
    {--no-format : Skip formatting the generated files with Vite+}')]
#[Description('Create a new public-site template from an existing one')]
class MakeTemplateCommand extends Command
{
    public function handle(TemplateScaffolder $scaffolder, TemplateRegistry $registry): int
    {
        $id = (string) $this->argument('id');
        $from = (string) $this->option('from');

        try {
            $paths = $scaffolder->scaffold(
                id: $id,
                from: $from,
                name: $this->option('name') ?: null,
                description: $this->option('description') ?: null,
                author: $this->option('author') ?: null,
            );
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
            $this->format([...$paths['created'], ...$paths['updated']]);
        }

        $this->components->info("Template [{$id}] created from [{$from}].");
        $this->components->bulletList([
            'Run `composer dev` (or `npm run build:ssr` for SSR) so Vite picks up the new pages.',
            "Preview it at /?template={$id} while signed in to the panel.",
            "Replace public/templates/{$id}.webp with a real screenshot (960 × 600).",
            'Run `composer ci:check`: every page of the new template is tested automatically.',
        ]);

        return self::SUCCESS;
    }

    /**
     * Rewritten import paths can change line lengths, so format the generated files the way CI checks them.
     *
     * @param  list<string>  $paths
     */
    private function format(array $paths): void
    {
        $result = Process::path(base_path())->timeout(120)->run(['npx', 'vp', 'fmt', ...$paths]);

        if ($result->failed()) {
            $this->components->warn('Could not format the generated files; run `npm run check:fix`.');
        }
    }
}
