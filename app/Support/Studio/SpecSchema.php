<?php

namespace App\Support\Studio;

/**
 * Builds the JSON Schema (draft 2020-12) of a Template Spec from SpecCatalogue. Written to
 * resources/studio/schema/v1.json by `php artisan studio:generate`; the AI receives it as its
 * output format (P9). SpecValidator remains the authority: it also checks what JSON Schema cannot
 * express clearly (a home section used twice).
 */
final class SpecSchema
{
    private const HEX_COLOR = '^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$';

    /**
     * @return array<string, mixed>
     */
    public static function toArray(): array
    {
        return [
            '$schema' => 'https://json-schema.org/draft/2020-12/schema',
            '$id' => 'studio/v1',
            'title' => 'Template Spec (studio/v1)',
            'description' => 'A studio template: design tokens, layout, the sections of each page and optional scoped CSS. Content never lives here; it comes from the panel.',
            ...self::object([
                '$schema' => ['const' => SpecCatalogue::VERSION],
                'name' => ['type' => 'string', 'minLength' => 1, 'maxLength' => SpecCatalogue::limit('name')],
                'tokens' => self::tokens(),
                'layout' => self::layout(),
                'pages' => self::pages(),
                'copy' => self::copy(),
                'css' => [
                    'type' => 'string',
                    'maxLength' => SpecCatalogue::limit('css'),
                    'description' => 'CSS scoped to the template; sanitised on save (no @import, no external url(), no scripts).',
                ],
            ], optional: ['copy', 'css']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function tokens(): array
    {
        $palette = self::object(array_fill_keys(SpecCatalogue::COLOR_ROLES, ['type' => 'string', 'pattern' => self::HEX_COLOR]));
        $fonts = [];

        foreach (array_keys(SpecCatalogue::FONT_ROLES) as $role) {
            $fonts[$role] = ['enum' => SpecCatalogue::fontsFor($role)];
        }

        return self::object([
            'colors' => self::object(['light' => $palette, 'dark' => $palette]),
            'fonts' => self::object($fonts),
            ...array_map(fn (array $values): array => ['enum' => $values], SpecCatalogue::TOKENS),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function layout(): array
    {
        return self::object([
            ...array_map(fn (array $variants): array => self::variant($variants), SpecCatalogue::LAYOUT),
            'container' => ['enum' => SpecCatalogue::CONTAINERS],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function pages(): array
    {
        $sections = [];

        foreach (SpecCatalogue::HOME_SECTIONS as $name => $rule) {
            $props = array_map(fn (array $prop): array => $prop['type'] === 'integer'
                ? ['type' => 'integer', 'minimum' => $prop['min'], 'maximum' => $prop['max']]
                : ['type' => 'boolean'], $rule['props']);

            $sections[] = self::object([
                'section' => ['const' => $name],
                'variant' => ['enum' => $rule['variants']],
                ...($props === [] ? [] : ['props' => self::object($props, optional: array_keys($props))]),
            ], optional: ['props']);
        }

        return self::object([
            'home' => [
                'type' => 'array',
                'minItems' => 1,
                'maxItems' => SpecCatalogue::limit('home_sections'),
                'description' => 'Home page sections in display order; each section at most once.',
                'items' => ['oneOf' => $sections],
            ],
            ...array_map(fn (array $variants): array => self::variant($variants), SpecCatalogue::PAGES),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private static function copy(): array
    {
        return self::object(
            array_map(fn (int $max): array => ['type' => 'string', 'minLength' => 1, 'maxLength' => $max], SpecCatalogue::COPY),
            optional: array_keys(SpecCatalogue::COPY),
        );
    }

    /**
     * @param  list<string>  $variants
     * @return array<string, mixed>
     */
    private static function variant(array $variants): array
    {
        return self::object(['variant' => ['enum' => $variants]]);
    }

    /**
     * A closed object: every property required except the optional ones, nothing else allowed.
     *
     * @param  array<string, mixed>  $properties
     * @param  list<string>  $optional
     * @return array<string, mixed>
     */
    private static function object(array $properties, array $optional = []): array
    {
        return [
            'type' => 'object',
            'properties' => $properties,
            'required' => array_values(array_diff(array_keys($properties), $optional)),
            'additionalProperties' => false,
        ];
    }
}
