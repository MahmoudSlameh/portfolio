<?php

namespace App\Support\Studio;

/**
 * Turns a spec's design tokens into the CSS the studio engine renders with.
 *
 * The tokens feed the shared colour system of every template (`--paper`, `--ink`, `--signal` …, see
 * resources/js/styles/base.css), so Tailwind utilities and the kit follow the spec, plus a few
 * engine variables (`--st-radius`, `--st-shadow`, `--st-section-gap`, `--st-container`, …).
 * Printed in <head> after the bundle so server-rendered pages paint with the right colours, followed
 * by the spec's own (already sanitised) CSS.
 *
 * Every value comes from a closed list or a validated hex colour, so nothing here can inject CSS.
 */
final class StudioStyles
{
    private const RADIUS = ['none' => '0', 'sm' => '0.25rem', 'md' => '0.5rem', 'lg' => '1rem', 'full' => '1.75rem'];

    private const SECTION_GAP = ['compact' => '3.5rem', 'comfortable' => '6rem', 'airy' => '9rem'];

    private const CONTAINER = ['narrow' => '44rem', 'default' => '68rem', 'wide' => '84rem'];

    private const MOTION = ['none' => '0s', 'subtle' => '0.2s', 'lively' => '0.45s'];

    /**
     * @param  array<string, mixed>  $spec  A validated Template Spec
     */
    public static function render(array $spec): string
    {
        /** @var array{colors: array{light: array<string, string>, dark: array<string, string>}, fonts: array<string, string>, radius: string, density: string, shadow: string, motion: string} $tokens */
        $tokens = $spec['tokens'];
        /** @var array{container: string} $layout */
        $layout = $spec['layout'];
        $fonts = SpecCatalogue::fonts();
        $scope = CssSanitizer::SCOPE;

        $base = [
            '--tpl-font-display' => $fonts[$tokens['fonts']['display']]['family'] ?? 'ui-sans-serif, system-ui, sans-serif',
            '--tpl-font-sans' => $fonts[$tokens['fonts']['body']]['family'] ?? 'ui-sans-serif, system-ui, sans-serif',
            '--tpl-font-mono' => $fonts[$tokens['fonts']['mono']]['family'] ?? 'ui-monospace, monospace',
            '--st-radius' => self::RADIUS[$tokens['radius']] ?? '0.5rem',
            '--st-section-gap' => self::SECTION_GAP[$tokens['density']] ?? '6rem',
            '--st-container' => self::CONTAINER[$layout['container']] ?? '68rem',
            '--st-duration' => self::MOTION[$tokens['motion']] ?? '0.2s',
            '--header-height' => '4.5rem',
        ];

        $css = self::block($scope, [...$base, ...self::palette($tokens['colors']['light'], $tokens['shadow'])]);
        $css .= self::block("{$scope}[data-theme=\"dark\"]", self::palette($tokens['colors']['dark'], $tokens['shadow']));

        if (isset($spec['css']) && is_string($spec['css']) && $spec['css'] !== '') {
            $css .= "/* spec css */\n".$spec['css'];
        }

        // Defence in depth: sanitised CSS never contains "<", tokens cannot either.
        return str_replace('<', '', $css);
    }

    /**
     * @param  array<string, string>  $colors  bg, surface, text, muted, accent, border
     * @return array<string, string>
     */
    private static function palette(array $colors, string $shadow): array
    {
        $accent = $colors['accent'];

        return [
            '--paper' => $colors['bg'],
            '--surface' => $colors['surface'],
            '--raised' => $colors['surface'],
            '--ink' => $colors['text'],
            '--ink-muted' => $colors['muted'],
            '--ink-subtle' => $colors['muted'],
            '--line' => $colors['border'],
            '--line-strong' => $colors['text'],
            '--signal' => $accent,
            '--signal-ink' => $accent,
            '--signal-soft' => "color-mix(in srgb, {$accent} 14%, transparent)",
            '--on-signal' => self::readableOn($accent),
            '--electric' => $accent,
            '--danger' => '#dc2626',
            '--danger-soft' => 'rgb(220 38 38 / 0.1)',
            '--st-shadow' => match ($shadow) {
                'soft' => '0 18px 40px -18px rgb(0 0 0 / 0.35)',
                'hard' => "4px 4px 0 {$colors['text']}",
                default => 'none',
            },
            '--shadow-lift' => $shadow === 'none' ? 'none' : '0 18px 40px -18px rgb(0 0 0 / 0.35)',
        ];
    }

    /**
     * Black or white text, whichever reads better on the colour (WCAG relative luminance).
     */
    public static function readableOn(string $hex): string
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        $channel = function (string $pair): float {
            $value = hexdec($pair) / 255;

            return $value <= 0.03928 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        };

        $luminance = 0.2126 * $channel(substr($hex, 0, 2)) + 0.7152 * $channel(substr($hex, 2, 2)) + 0.0722 * $channel(substr($hex, 4, 2));

        // Contrast against black is (L + 0.05) / 0.05; against white 1.05 / (L + 0.05).
        return ($luminance + 0.05) / 0.05 >= 1.05 / ($luminance + 0.05) ? '#000000' : '#ffffff';
    }

    /**
     * @param  array<string, string>  $declarations
     */
    private static function block(string $selector, array $declarations): string
    {
        $lines = array_map(fn (string $property, string $value): string => "  {$property}: {$value};", array_keys($declarations), $declarations);

        return "{$selector} {\n".implode("\n", $lines)."\n}\n";
    }
}
