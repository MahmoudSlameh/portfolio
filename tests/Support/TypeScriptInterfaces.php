<?php

namespace Tests\Support;

use RuntimeException;

/**
 * Reads top-level property names of the interfaces in resources/js/types/content.ts,
 * so tests can assert the PHP resources send exactly the shape the templates expect.
 */
final class TypeScriptInterfaces
{
    /**
     * @var array<string, array{extends: string|null, omit: list<string>, required: list<string>, optional: list<string>}>|null
     */
    private static ?array $interfaces = null;

    /**
     * @return array{required: list<string>, optional: list<string>}
     */
    public static function keys(string $interface): array
    {
        $interfaces = self::parse();

        if (! isset($interfaces[$interface])) {
            throw new RuntimeException("Interface [{$interface}] not found in content.ts.");
        }

        $definition = $interfaces[$interface];
        $required = $definition['required'];
        $optional = $definition['optional'];

        if ($definition['extends'] !== null) {
            $parent = self::keys($definition['extends']);
            $required = [...array_diff($parent['required'], $definition['omit']), ...$required];
            $optional = [...array_diff($parent['optional'], $definition['omit']), ...$optional];
        }

        return ['required' => array_values(array_unique($required)), 'optional' => array_values(array_unique($optional))];
    }

    /**
     * @return array<string, array{extends: string|null, omit: list<string>, required: list<string>, optional: list<string>}>
     */
    private static function parse(): array
    {
        if (self::$interfaces !== null) {
            return self::$interfaces;
        }

        $source = (string) file_get_contents(dirname(__DIR__, 2).'/resources/js/types/content.ts');
        preg_match_all('/export interface (\w+)(?: extends ([^{]+))? \{\n(.*?)\n\}/s', $source, $matches, PREG_SET_ORDER);

        $interfaces = [];

        foreach ($matches as [, $name, $extends, $body]) {
            $parent = null;
            $omit = [];

            if (trim($extends) !== '') {
                if (preg_match("/Omit<(\w+),\s*([^>]+)>/", $extends, $omitMatch)) {
                    $parent = $omitMatch[1];
                    preg_match_all("/'(\w+)'/", $omitMatch[2], $omitted);
                    $omit = $omitted[1];
                } else {
                    $parent = trim($extends);
                }
            }

            preg_match_all('/^    (\w+)(\??):/m', $body, $properties, PREG_SET_ORDER);

            $interfaces[$name] = [
                'extends' => $parent,
                'omit' => $omit,
                'required' => array_values(array_map(fn (array $p): string => $p[1], array_filter($properties, fn (array $p): bool => $p[2] === ''))),
                'optional' => array_values(array_map(fn (array $p): string => $p[1], array_filter($properties, fn (array $p): bool => $p[2] === '?'))),
            ];
        }

        return self::$interfaces = $interfaces;
    }
}
