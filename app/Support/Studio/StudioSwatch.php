<?php

namespace App\Support\Studio;

/**
 * A small picture of a spec for the Appearance card when there is no screenshot (P10-03): the light and
 * dark palettes side by side, a heading in the display font, the radius, and the main layout choices.
 * Drawn as SVG from validated tokens, so it needs nothing installed.
 */
final class StudioSwatch
{
    private const RADIUS = ['none' => 0, 'sm' => 4, 'md' => 8, 'lg' => 14, 'full' => 22];

    /**
     * @param  array<string, mixed>  $spec  A valid Template Spec
     */
    public static function svg(array $spec): string
    {
        /** @var array{colors: array{light: array<string, string>, dark: array<string, string>}, fonts: array<string, string>, radius?: string} $tokens */
        $tokens = $spec['tokens'];
        $fonts = SpecCatalogue::fonts();
        $display = $fonts[$tokens['fonts']['display'] ?? '']['family'] ?? 'ui-sans-serif, system-ui, sans-serif';
        $body = $fonts[$tokens['fonts']['body'] ?? '']['family'] ?? 'ui-sans-serif, system-ui, sans-serif';
        $radius = self::RADIUS[$tokens['radius'] ?? 'md'] ?? 8;
        $label = self::label($spec);

        $halves = self::half($tokens['colors']['light'], 0, $display, $body, $radius, (string) ($spec['name'] ?? ''))
            .self::half($tokens['colors']['dark'], 320, $display, $body, $radius, (string) ($spec['name'] ?? ''));

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 360" width="640" height="360" role="img">'
            .$halves
            .'<rect x="0" y="328" width="640" height="32" fill="#00000099"/>'
            .'<text x="16" y="349" fill="#ffffff" font-family="ui-sans-serif, system-ui, sans-serif" font-size="12">'.self::e($label).'</text>'
            .'</svg>';
    }

    /**
     * @param  array<string, mixed>  $spec
     */
    public static function dataUri(array $spec): string
    {
        return 'data:image/svg+xml;base64,'.base64_encode(self::svg($spec));
    }

    /**
     * @param  array<string, string>  $c  Colour roles of one theme
     */
    private static function half(array $c, int $x, string $display, string $body, int $radius, string $name): string
    {
        $pill = min($radius, 14);

        return '<g transform="translate('.$x.' 0)">'
            .'<rect width="320" height="360" fill="'.self::color($c['bg'] ?? null).'"/>'
            // Header bar
            .'<rect x="16" y="16" width="288" height="28" rx="'.min($radius, 14).'" fill="'.self::color($c['surface'] ?? null).'" stroke="'.self::color($c['border'] ?? null).'"/>'
            .'<circle cx="34" cy="30" r="6" fill="'.self::color($c['accent'] ?? null).'"/>'
            .'<rect x="200" y="26" width="88" height="8" rx="4" fill="'.self::color($c['muted'] ?? null).'"/>'
            // Heading and text
            .'<text x="20" y="98" fill="'.self::color($c['text'] ?? null).'" font-family="'.self::e($display).'" font-size="30" font-weight="700">'.self::e(mb_strimwidth($name !== '' ? $name : 'Aa', 0, 16, '…')).'</text>'
            .'<text x="20" y="124" fill="'.self::color($c['muted'] ?? null).'" font-family="'.self::e($body).'" font-size="13">The quick brown fox jumps over</text>'
            // Button
            .'<rect x="20" y="142" width="96" height="30" rx="'.$pill.'" fill="'.self::color($c['accent'] ?? null).'"/>'
            // Cards
            .'<rect x="20" y="192" width="134" height="116" rx="'.$radius.'" fill="'.self::color($c['surface'] ?? null).'" stroke="'.self::color($c['border'] ?? null).'"/>'
            .'<rect x="166" y="192" width="134" height="116" rx="'.$radius.'" fill="'.self::color($c['surface'] ?? null).'" stroke="'.self::color($c['border'] ?? null).'"/>'
            .'<rect x="34" y="276" width="70" height="8" rx="4" fill="'.self::color($c['text'] ?? null).'"/>'
            .'<rect x="180" y="276" width="70" height="8" rx="4" fill="'.self::color($c['text'] ?? null).'"/>'
            .'</g>';
    }

    /**
     * @param  array<string, mixed>  $spec
     */
    private static function label(array $spec): string
    {
        $header = $spec['layout']['header']['variant'] ?? null;
        $hero = null;

        foreach ((array) ($spec['pages']['home'] ?? []) as $section) {
            if (is_array($section) && ($section['section'] ?? null) === 'hero') {
                $hero = $section['variant'] ?? null;
            }
        }

        $parts = array_filter([
            is_string($header) ? "Header: {$header}" : null,
            is_string($hero) ? "Hero: {$hero}" : null,
            'Radius: '.($spec['tokens']['radius'] ?? 'md'),
            'Light / dark',
        ]);

        return implode(' · ', $parts);
    }

    /**
     * Colours are validated as #rrggbb; anything else falls back to grey.
     */
    private static function color(mixed $value): string
    {
        return is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value) === 1 ? $value : '#888888';
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
