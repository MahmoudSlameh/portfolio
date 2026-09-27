<?php

namespace App\Support\Studio;

use App\Models\StudioTemplate;
use App\Models\StudioTemplateVersion;
use Illuminate\Support\Str;

/**
 * The shareable file of a studio template (`<slug>.studio.json`, docs/templates/sharing-studio-templates.md):
 * the spec plus a name and description. Never media, prompts, provider or usage data.
 */
final class StudioFile
{
    public const FORMAT = 'portfolio-studio-template';

    public const FORMAT_VERSION = 1;

    /** Largest file accepted on import, in bytes. */
    public const MAX_BYTES = 100 * 1024;

    /**
     * @return array{format: string, formatVersion: int, name: string, description: string|null, exportedAt: string, spec: array<string, mixed>}
     */
    public static function export(StudioTemplate $template, StudioTemplateVersion $version): array
    {
        return [
            'format' => self::FORMAT,
            'formatVersion' => self::FORMAT_VERSION,
            'name' => $template->name,
            'description' => $template->description,
            'exportedAt' => now()->toIso8601String(),
            'spec' => $version->spec,
        ];
    }

    public static function json(StudioTemplate $template, StudioTemplateVersion $version): string
    {
        return (string) json_encode(self::export($template, $version), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n";
    }

    public static function filename(StudioTemplate $template, ?StudioTemplateVersion $version = null): string
    {
        $slug = Str::slug($template->name) ?: 'studio-template';

        return $slug.($version !== null && $version->id !== $template->active_version_id ? "-v{$version->number}" : '').'.studio.json';
    }

    /**
     * Read an exported file (or a bare spec) and validate its spec.
     *
     * @return array{name: string|null, description: string|null, spec: array<string, mixed>}
     *
     * @throws InvalidStudioFile with a message for the owner (validation errors listed)
     */
    public static function parse(string $contents): array
    {
        if (strlen($contents) > self::MAX_BYTES) {
            throw new InvalidStudioFile('The file is larger than '.intdiv(self::MAX_BYTES, 1024).' KB.');
        }

        $data = json_decode($contents, true);

        if (! is_array($data) || array_is_list($data)) {
            throw new InvalidStudioFile('The file is not a JSON object.');
        }

        if (($data['$schema'] ?? null) === SpecCatalogue::VERSION) {
            $data = ['spec' => $data];
        } elseif (($data['format'] ?? null) !== self::FORMAT) {
            throw new InvalidStudioFile('This is not a studio template file (expected "format": "'.self::FORMAT.'" or a spec with "$schema": "'.SpecCatalogue::VERSION.'").');
        } elseif (($data['formatVersion'] ?? null) !== self::FORMAT_VERSION) {
            throw new InvalidStudioFile('This file was made by a newer version of the app (format version '.json_encode($data['formatVersion'] ?? null).').');
        }

        $spec = $data['spec'] ?? null;

        if (! is_array($spec) || array_is_list($spec)) {
            throw new InvalidStudioFile('The file has no "spec" object.');
        }

        $result = (new SpecValidator)->validate($spec);

        if (! $result->passes()) {
            throw new InvalidStudioFile('The spec is invalid: '.implode(' · ', array_map('trim', $result->messages())), $result->messages());
        }

        /** @var array<string, mixed> $spec */
        return [
            'name' => self::text($data['name'] ?? $spec['name'] ?? null, SpecCatalogue::limit('name')),
            'description' => self::text($data['description'] ?? null, 255),
            'spec' => $spec,
        ];
    }

    private static function text(mixed $value, int $limit): ?string
    {
        return is_string($value) && trim($value) !== '' ? Str::limit(trim(strip_tags($value)), $limit, '') : null;
    }
}
