<?php

namespace App\Support\Templates;

use App\Models\StudioTemplate;
use App\Models\StudioTemplateVersion;
use App\Support\Studio\SpecCatalogue;
use InvalidArgumentException;

/**
 * One public site template.
 *
 * - Code templates come from a `template.json` manifest; their id is also their namespace.
 * - Studio templates come from the database (`studio:<ulid>`) and all share the `studio` namespace.
 *
 * The namespace is the Inertia page folder (e.g. "terminal/Home") and the `data-template`
 * attribute on <html>; the id is what site_settings.active_template stores.
 */
final readonly class TemplateDefinition
{
    /**
     * @param  list<string>  $preloadFonts  Above-the-fold font files (Vite manifest keys) preloaded in the document head.
     * @param  string|null  $screenshot  Public path or absolute URL of the screenshot shown on the Appearance page.
     * @param  'code'|'studio'  $kind
     */
    public function __construct(
        public string $id,
        public string $label,
        public string $description,
        public array $preloadFonts,
        public ?string $screenshot,
        public ?string $author = null,
        public string $kind = 'code',
        public string $namespace = '',
    ) {}

    /**
     * The Inertia namespace and `data-template` value (the id for code templates).
     */
    public function namespace(): string
    {
        return $this->namespace !== '' ? $this->namespace : $this->id;
    }

    public function screenshotUrl(): ?string
    {
        if ($this->screenshot === null) {
            return null;
        }

        return preg_match('#^(https?:)?//#', $this->screenshot) === 1 ? $this->screenshot : asset($this->screenshot);
    }

    public function isStudio(): bool
    {
        return $this->kind === 'studio';
    }

    /**
     * A ready studio template, with the fonts its active spec uses preloaded.
     */
    public static function forStudio(StudioTemplate $template): self
    {
        $version = $template->getRelationValue('activeVersion');
        /** @var array{tokens?: array{fonts?: array<string, string>}} $spec */
        $spec = $version instanceof StudioTemplateVersion ? $version->spec : [];
        $fonts = SpecCatalogue::fonts();
        $preload = array_values(array_unique(array_filter(array_map(
            fn (string $font): ?string => $fonts[$font]['preload'] ?? null,
            array_values($spec['tokens']['fonts'] ?? []),
        ))));

        return new self(
            id: $template->templateId(),
            label: $template->name,
            description: $template->description ?? 'A studio template.',
            preloadFonts: $preload,
            screenshot: $template->getFirstMediaUrl('screenshot') ?: null,
            kind: 'studio',
            namespace: 'studio',
        );
    }

    /**
     * @param  array<string, mixed>  $manifest
     *
     * @throws InvalidArgumentException when a required field is missing or has the wrong type
     */
    public static function fromManifest(array $manifest, string $source): self
    {
        foreach (['id', 'name', 'description', 'screenshot'] as $key) {
            if (! is_string($manifest[$key] ?? null) || trim($manifest[$key]) === '') {
                throw new InvalidArgumentException("Template manifest {$source}: \"{$key}\" must be a non-empty string.");
            }
        }

        $fonts = $manifest['preloadFonts'] ?? [];

        if (! is_array($fonts) || ! array_is_list($fonts) || array_filter($fonts, fn (mixed $font): bool => ! is_string($font)) !== []) {
            throw new InvalidArgumentException("Template manifest {$source}: \"preloadFonts\" must be a list of strings.");
        }

        if (preg_match('/^[a-z][a-z0-9-]*$/', $manifest['id']) !== 1) {
            throw new InvalidArgumentException("Template manifest {$source}: \"id\" must be a lowercase slug.");
        }

        /** @var list<string> $fonts */
        return new self(
            id: $manifest['id'],
            label: $manifest['name'],
            description: $manifest['description'],
            preloadFonts: $fonts,
            screenshot: $manifest['screenshot'],
            author: is_string($manifest['author'] ?? null) ? $manifest['author'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->label,
            'description' => $this->description,
            'author' => $this->author,
            'preloadFonts' => $this->preloadFonts,
            'screenshot' => $this->screenshot,
        ];
    }
}
