<?php

namespace App\Support\Templates;

use InvalidArgumentException;
use RuntimeException;

/**
 * Every template the public site can render, discovered from `<path>/<id>/template.json`.
 *
 * Manifests are read once per process; `php artisan template:cache` (run by `php artisan optimize`)
 * writes them to a PHP file so production never touches the JSON. See docs/11-template-kit.md.
 */
final class TemplateRegistry
{
    /**
     * @var array<string, TemplateDefinition>|null
     */
    private ?array $templates = null;

    public function __construct(
        private readonly string $path,
        private readonly string $defaultId,
        private readonly string $cachePath,
    ) {}

    /**
     * @return array<string, TemplateDefinition> keyed by id, sorted by id
     */
    public function all(): array
    {
        return $this->templates ??= $this->load();
    }

    /**
     * @return list<string>
     */
    public function ids(): array
    {
        return array_keys($this->all());
    }

    public function has(?string $id): bool
    {
        return $id !== null && isset($this->all()[$id]);
    }

    public function find(?string $id): ?TemplateDefinition
    {
        return $id === null ? null : ($this->all()[$id] ?? null);
    }

    /**
     * The configured default template, or the first one when the default does not exist.
     */
    public function default(): TemplateDefinition
    {
        return $this->find($this->defaultId)
            ?? array_values($this->all())[0]
            ?? throw new RuntimeException("No templates found in {$this->path}.");
    }

    /**
     * Read every manifest from disk, ignoring the cache.
     *
     * @return array<string, TemplateDefinition>
     *
     * @throws InvalidArgumentException when a manifest is invalid or does not match its folder name
     */
    public function discover(): array
    {
        $templates = [];

        foreach (glob($this->path.'/*/template.json') ?: [] as $file) {
            $manifest = json_decode((string) file_get_contents($file), true);

            if (! is_array($manifest)) {
                throw new InvalidArgumentException("Template manifest {$file} is not valid JSON.");
            }

            /** @var array<string, mixed> $manifest */
            $template = TemplateDefinition::fromManifest($manifest, $file);

            if ($template->id !== basename(dirname($file))) {
                throw new InvalidArgumentException("Template manifest {$file}: \"id\" must match its folder name.");
            }

            $templates[$template->id] = $template;
        }

        ksort($templates);

        return $templates;
    }

    /**
     * Forget the templates read so far (after a template was added), keeping a warm cache warm.
     */
    public function refresh(): void
    {
        $this->templates = null;

        if (is_file($this->cachePath)) {
            $this->cache();
        }
    }

    public function cache(): void
    {
        $manifests = array_map(fn (TemplateDefinition $template): array => $template->toArray(), $this->discover());

        file_put_contents($this->cachePath, '<?php return '.var_export($manifests, true).';'.PHP_EOL);

        $this->templates = null;
    }

    public function clearCache(): void
    {
        if (is_file($this->cachePath)) {
            unlink($this->cachePath);
        }

        $this->templates = null;
    }

    /**
     * @return array<string, TemplateDefinition>
     */
    private function load(): array
    {
        if (! is_file($this->cachePath)) {
            return $this->discover();
        }

        /** @var array<string, array<string, mixed>> $manifests */
        $manifests = require $this->cachePath;

        return array_map(fn (array $manifest): TemplateDefinition => TemplateDefinition::fromManifest($manifest, $this->cachePath), $manifests);
    }
}
