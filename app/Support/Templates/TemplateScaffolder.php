<?php

namespace App\Support\Templates;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Creates a new code template by copying an existing one (the `minimal` starter by default) and
 * renaming everything that ties the copy to its source: the folder, the manifest, the Inertia page
 * wrappers, the CSS scope, the layout component and the stylesheet import. See docs/11-template-kit.md.
 */
final class TemplateScaffolder
{
    /** Ids that are taken by other features (studio templates are `studio:<ulid>`, P8). */
    private const RESERVED = ['studio', 'reset'];

    public function __construct(
        private readonly Filesystem $files,
        private readonly TemplateRegistry $registry,
        private readonly string $templatesPath,
        private readonly string $pagesPath,
        private readonly string $stylesheet,
        private readonly string $screenshotsPath,
    ) {}

    /**
     * @return array{created: list<string>, updated: list<string>}
     *
     * @throws InvalidArgumentException when the id is invalid or taken, or the source does not exist
     */
    public function scaffold(string $id, string $from = 'minimal', ?string $name = null, ?string $description = null, ?string $author = null): array
    {
        $this->validate($id, $from);

        $source = $this->registry->find($from) ?? throw new InvalidArgumentException("There is no template \"{$from}\".");
        $templateDir = "{$this->templatesPath}/{$id}";
        $pagesDir = "{$this->pagesPath}/{$id}";
        $replacements = $this->replacements($from, $id, $name ?: Str::headline($id));

        $this->files->copyDirectory("{$this->templatesPath}/{$from}", $templateDir);
        $this->files->delete("{$templateDir}/README.md");

        foreach ($this->files->allFiles($templateDir) as $file) {
            $path = $file->getPathname();
            $renamed = str_replace(Str::studly($from).'Layout', Str::studly($id).'Layout', $path);

            $this->files->put($renamed, strtr($file->getContents(), $replacements));

            if ($renamed !== $path) {
                $this->files->delete($path);
            }
        }

        $this->files->ensureDirectoryExists($pagesDir);

        foreach ($this->files->files("{$this->pagesPath}/{$from}") as $file) {
            $this->files->put("{$pagesDir}/{$file->getFilename()}", strtr($file->getContents(), $replacements));
        }

        $manifest = [
            'id' => $id,
            'name' => $name ?: Str::headline($id),
            'description' => $description ?: "A new template based on {$source->label}.",
            ...($author ? ['author' => $author] : []),
            'preloadFonts' => $source->preloadFonts,
            'screenshot' => "templates/{$id}.webp",
        ];
        $this->files->put("{$templateDir}/template.json", json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL);
        $this->files->put("{$templateDir}/README.md", $this->readme($manifest['name'], $id, $from));

        $screenshot = "{$this->screenshotsPath}/{$id}.webp";
        $this->files->copy("{$this->screenshotsPath}/{$from}.webp", $screenshot);

        $this->importStylesheet($from, $id);

        return ['created' => [$templateDir, $pagesDir, $screenshot], 'updated' => [$this->stylesheet]];
    }

    private function validate(string $id, string $from): void
    {
        $this->assertNewId($id);

        if (! $this->registry->has($from)) {
            throw new InvalidArgumentException("There is no template \"{$from}\" to copy. Available: ".implode(', ', $this->registry->ids()).'.');
        }
    }

    /**
     * A new code template id: a lowercase slug, not reserved, not taken (also used by template:eject).
     *
     * @throws InvalidArgumentException
     */
    public function assertNewId(string $id): void
    {
        if (preg_match('/^[a-z][a-z0-9-]*$/', $id) !== 1) {
            throw new InvalidArgumentException("\"{$id}\" is not a valid template id: use lowercase letters, digits and dashes, starting with a letter.");
        }

        if (in_array($id, self::RESERVED, true)) {
            throw new InvalidArgumentException("\"{$id}\" is reserved.");
        }

        if ($this->registry->has($id) || $this->files->exists("{$this->templatesPath}/{$id}") || $this->files->exists("{$this->pagesPath}/{$id}")) {
            throw new InvalidArgumentException("A template named \"{$id}\" already exists.");
        }
    }

    /**
     * @return array<string, string>
     */
    private function replacements(string $from, string $id, string $name): array
    {
        return [
            "@/templates/{$from}/" => "@/templates/{$id}/",
            "resources/js/templates/{$from}" => "resources/js/templates/{$id}",
            "resources/js/pages/{$from}" => "resources/js/pages/{$id}",
            Str::headline($from).' template' => "{$name} template",
            "data-template='{$from}'" => "data-template='{$id}'",
            "data-template=\"{$from}\"" => "data-template=\"{$id}\"",
            Str::studly($from).'Layout' => Str::studly($id).'Layout',
        ];
    }

    /**
     * Add `@import '../templates/<id>/styles.css';` after the source template's import (all template
     * stylesheets ship in one bundle, scoped by `data-template`; decision D18).
     */
    public function importStylesheet(string $from, string $id): void
    {
        $css = $this->files->get($this->stylesheet);
        $import = "@import '../templates/{$id}/styles.css';";
        $sourceImport = "@import '../templates/{$from}/styles.css';";

        if (str_contains($css, $import)) {
            return;
        }

        $css = str_contains($css, $sourceImport)
            ? str_replace($sourceImport, $sourceImport.PHP_EOL.$import, $css)
            : rtrim($css).PHP_EOL.$import.PHP_EOL;

        $this->files->put($this->stylesheet, $css);
    }

    private function readme(string $name, string $id, string $from): string
    {
        return <<<MD
            # {$name}

            Created with `php artisan make:template {$id} --from={$from}`.

            Next steps:

            1. Run `composer dev` and preview it at `/?template={$id}` while signed in to the panel.
            2. Change the design: tokens in `styles.css`, markup in `layout/` and `pages/`.
               Behaviour (contact form, filters, search, theme, article blocks) comes from `@/kit`.
            3. Replace the placeholder screenshot `public/templates/{$id}.webp` (960 × 600).
            4. Update `template.json` (name, description, fonts to preload).
            5. Run `composer ci:check`: every page of the template is tested automatically.

            Guide: docs/templates/building-a-template.md.

            MD;
    }
}
