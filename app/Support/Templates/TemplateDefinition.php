<?php

namespace App\Support\Templates;

use InvalidArgumentException;

/**
 * One public site template, read from its `template.json` manifest.
 *
 * The id is also the Inertia page namespace (e.g. "terminal/Home") and the `data-template`
 * attribute on <html>.
 */
final readonly class TemplateDefinition
{
    /**
     * @param  list<string>  $preloadFonts  Above-the-fold font files (Vite manifest keys) preloaded in the document head.
     * @param  string  $screenshot  Public path of the screenshot shown on the Appearance page.
     */
    public function __construct(
        public string $id,
        public string $label,
        public string $description,
        public array $preloadFonts,
        public string $screenshot,
        public ?string $author = null,
    ) {}

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
