<?php

namespace App\Support\Studio;

/**
 * Validates a Template Spec (`studio/v1`) against SpecCatalogue.
 *
 * Nothing is silently ignored: unknown keys, sections, variants and props are errors, and every
 * error carries the path it applies to (`pages.home[2].variant`), so the AI can repair its output
 * and people editing a spec by hand know exactly what to fix. See docs/12-ai-templates.md §2.
 */
final class SpecValidator
{
    private const HEX_COLOR = '/^#(?:[0-9a-f]{3}|[0-9a-f]{6}|[0-9a-f]{8})$/i';

    /** @var list<SpecError> */
    private array $errors = [];

    public function validateJson(string $json): SpecValidationResult
    {
        $spec = json_decode($json, true);

        if (! is_array($spec) || json_last_error() !== JSON_ERROR_NONE) {
            return new SpecValidationResult([new SpecError('', 'is not valid JSON.')]);
        }

        return $this->validate($spec);
    }

    /**
     * @param  array<mixed>  $spec
     */
    public function validate(array $spec): SpecValidationResult
    {
        $this->errors = [];

        $spec = $this->object($spec, '', ['$schema', 'name', 'tokens', 'layout', 'pages'], ['copy', 'css']);

        if ($spec === null) {
            return new SpecValidationResult($this->errors);
        }

        if (($spec['$schema'] ?? null) !== SpecCatalogue::VERSION) {
            $this->error('$schema', 'must be "'.SpecCatalogue::VERSION.'".');
        }

        $this->text($spec['name'] ?? null, 'name', SpecCatalogue::limit('name'));
        $this->tokens($spec['tokens'] ?? null);
        $this->layout($spec['layout'] ?? null);
        $this->pages($spec['pages'] ?? null);

        if (array_key_exists('copy', $spec)) {
            $this->copy($spec['copy']);
        }

        if (array_key_exists('css', $spec)) {
            $this->css($spec['css']);
        }

        return new SpecValidationResult($this->errors);
    }

    private function tokens(mixed $tokens): void
    {
        $tokens = $this->object($tokens, 'tokens', ['colors', 'fonts', ...array_keys(SpecCatalogue::TOKENS)]);

        if ($tokens === null) {
            return;
        }

        $colors = $this->object($tokens['colors'] ?? null, 'tokens.colors', ['light', 'dark']);

        if ($colors !== null) {
            foreach (['light', 'dark'] as $theme) {
                $path = "tokens.colors.{$theme}";

                $palette = $this->object($colors[$theme] ?? null, $path, SpecCatalogue::COLOR_ROLES);

                if ($palette === null) {
                    continue;
                }

                foreach (SpecCatalogue::COLOR_ROLES as $role) {
                    $value = $palette[$role] ?? null;

                    if (array_key_exists($role, $palette) && (! is_string($value) || preg_match(self::HEX_COLOR, $value) !== 1)) {
                        $this->error("{$path}.{$role}", 'must be a hex colour like #1a1a1a.');
                    }
                }
            }
        }

        $fonts = $this->object($tokens['fonts'] ?? null, 'tokens.fonts', array_keys(SpecCatalogue::FONT_ROLES));

        if ($fonts !== null) {
            foreach (array_keys(SpecCatalogue::FONT_ROLES) as $role) {
                if (array_key_exists($role, $fonts)) {
                    $this->oneOf($fonts[$role], "tokens.fonts.{$role}", SpecCatalogue::fontsFor($role));
                }
            }
        }

        foreach (SpecCatalogue::TOKENS as $token => $values) {
            if (array_key_exists($token, $tokens)) {
                $this->oneOf($tokens[$token], "tokens.{$token}", $values);
            }
        }
    }

    private function layout(mixed $layout): void
    {
        $layout = $this->object($layout, 'layout', [...array_keys(SpecCatalogue::LAYOUT), 'container']);

        if ($layout === null) {
            return;
        }

        foreach (SpecCatalogue::LAYOUT as $part => $variants) {
            $this->variant($layout[$part] ?? null, "layout.{$part}", $variants);
        }

        if (array_key_exists('container', $layout)) {
            $this->oneOf($layout['container'], 'layout.container', SpecCatalogue::CONTAINERS);
        }
    }

    private function pages(mixed $pages): void
    {
        $pages = $this->object($pages, 'pages', ['home', ...array_keys(SpecCatalogue::PAGES)]);

        if ($pages === null) {
            return;
        }

        $this->home($pages['home'] ?? null);

        foreach (SpecCatalogue::PAGES as $page => $variants) {
            if (array_key_exists($page, $pages)) {
                $this->variant($pages[$page], "pages.{$page}", $variants);
            }
        }
    }

    private function home(mixed $sections): void
    {
        $max = SpecCatalogue::limit('home_sections');

        if (! is_array($sections) || ! array_is_list($sections) || $sections === []) {
            $this->error('pages.home', 'must be a non-empty list of sections.');

            return;
        }

        if (count($sections) > $max) {
            $this->error('pages.home', "may have at most {$max} sections.");
        }

        $seen = [];

        foreach ($sections as $index => $section) {
            $path = "pages.home[{$index}]";

            $section = $this->object($section, $path, ['section', 'variant'], ['props']);

            if ($section === null) {
                continue;
            }

            $name = $section['section'] ?? null;

            if (! is_string($name) || ! array_key_exists($name, SpecCatalogue::HOME_SECTIONS)) {
                $this->error("{$path}.section", 'must be one of: '.implode(', ', array_keys(SpecCatalogue::HOME_SECTIONS)).'.');

                continue;
            }

            if (isset($seen[$name])) {
                $this->error("{$path}.section", "\"{$name}\" is already used by pages.home[{$seen[$name]}]; each section can appear once.");
            }

            $seen[$name] = $index;
            $rule = SpecCatalogue::HOME_SECTIONS[$name];
            $this->oneOf($section['variant'] ?? null, "{$path}.variant", $rule['variants']);

            if (array_key_exists('props', $section)) {
                $this->props($section['props'], "{$path}.props", $rule['props']);
            }
        }
    }

    /**
     * @param  array<string, array{type: 'boolean'}|array{type: 'integer', min: int, max: int}>  $rules
     */
    private function props(mixed $props, string $path, array $rules): void
    {
        if ($rules === []) {
            $this->error($path, 'is not allowed: this section has no props.');

            return;
        }

        $props = $this->object($props, $path, [], array_keys($rules));

        if ($props === null) {
            return;
        }

        foreach ($props as $name => $value) {
            $rule = $rules[$name] ?? null;

            if ($rule === null) {
                continue; // already reported as "not allowed"
            }

            if ($rule['type'] === 'boolean' && ! is_bool($value)) {
                $this->error("{$path}.{$name}", 'must be true or false.');
            }

            if ($rule['type'] === 'integer' && (! is_int($value) || $value < $rule['min'] || $value > $rule['max'])) {
                $this->error("{$path}.{$name}", "must be a whole number from {$rule['min']} to {$rule['max']}.");
            }
        }
    }

    private function copy(mixed $copy): void
    {
        $copy = $this->object($copy, 'copy', [], array_keys(SpecCatalogue::COPY));

        if ($copy === null) {
            return;
        }

        foreach ($copy as $key => $value) {
            if (isset(SpecCatalogue::COPY[$key])) {
                $this->text($value, "copy.{$key}", SpecCatalogue::COPY[$key]);
            }
        }
    }

    private function css(mixed $css): void
    {
        $max = SpecCatalogue::limit('css');

        if (! is_string($css)) {
            $this->error('css', 'must be a string.');
        } elseif (strlen($css) > $max) {
            $this->error('css', 'may be at most '.intdiv($max, 1024).' KB.');
        }
    }

    /**
     * `{ "variant": "…" }` with nothing else.
     *
     * @param  list<string>  $variants
     */
    private function variant(mixed $value, string $path, array $variants): void
    {
        $object = $this->object($value, $path, ['variant']);

        if ($object !== null) {
            $this->oneOf($object['variant'] ?? null, "{$path}.variant", $variants);
        }
    }

    /**
     * Plain text: a non-empty string, at most $max characters, no markup or control characters.
     */
    private function text(mixed $value, string $path, int $max): void
    {
        if (! is_string($value) || trim($value) === '') {
            $this->error($path, 'must be a non-empty string.');
        } elseif (mb_strlen($value) > $max) {
            $this->error($path, "may be at most {$max} characters.");
        } elseif (preg_match('/[<>\x00-\x1F\x7F]/u', $value) === 1) {
            $this->error($path, 'must be plain text (no markup, line breaks or control characters).');
        }
    }

    /**
     * @param  list<string>  $allowed
     */
    private function oneOf(mixed $value, string $path, array $allowed): void
    {
        if (! is_string($value) || ! in_array($value, $allowed, true)) {
            $this->error($path, 'must be one of: '.implode(', ', $allowed).'.');
        }
    }

    /**
     * Checks that $value is a JSON object with every required key and no other keys than the
     * required and optional ones. Returns the object, or null when it is not an object at all.
     *
     * @param  list<string>  $required
     * @param  list<string>  $optional
     * @return array<string, mixed>|null
     */
    private function object(mixed $value, string $path, array $required, array $optional = []): ?array
    {
        if (! is_array($value) || ($value !== [] && array_is_list($value))) {
            $this->error($path === '' ? '(root)' : $path, 'must be an object.');

            return null;
        }

        $object = [];

        foreach ($value as $key => $item) {
            $object[(string) $key] = $item;
        }

        foreach ($required as $key) {
            if (! array_key_exists($key, $object)) {
                $this->error($this->join($path, $key), 'is required.');
            }
        }

        $allowed = [...$required, ...$optional];

        foreach (array_keys($object) as $key) {
            if (! in_array($key, $allowed, true)) {
                $this->error($this->join($path, $key), 'is not allowed'.($allowed === [] ? '.' : '; expected: '.implode(', ', $allowed).'.'));
            }
        }

        return $object;
    }

    private function join(string $path, string $key): string
    {
        return $path === '' ? $key : "{$path}.{$key}";
    }

    private function error(string $path, string $message): void
    {
        $this->errors[] = new SpecError($path, $message);
    }
}
