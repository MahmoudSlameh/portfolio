<?php

namespace App\Support\Studio;

/**
 * Makes the `css` of a Template Spec safe to print inside a <style> tag.
 *
 * Allow-list based: the stylesheet is parsed into rules and declarations, and only what is
 * understood and harmless is written back out. Everything else is dropped and reported in
 * CssSanitizeResult::$removed (shown to the owner, sent back to the AI to repair).
 *
 * - Rules: style rules, `@media`, `@supports` and `@keyframes`. Every other at-rule (`@import`,
 *   `@font-face`, `@charset`, `@namespace`, `@page`, …) and nested rules are dropped.
 * - Selectors are scoped to the studio template: `.x` → `[data-template="studio"] .x`,
 *   `html`/`:root` → the scope itself, `[data-theme="dark"] .x` → `[data-template="studio"][data-theme="dark"] .x`.
 * - Declarations: no `expression()`, `javascript:`, `behavior`, `-moz-binding`, backslash escapes
 *   (they can hide any of these), or loading anything: `url()` only for inline `data:` images up to
 *   20 KB; `image-set()`, `image()`, `element()`, `src()` and `cross-fade()` are dropped.
 * - `<` never survives, so the output cannot close the <style> tag.
 *
 * The result is stable: sanitising sanitised CSS returns it unchanged.
 */
final class CssSanitizer
{
    public const SCOPE = '[data-template="studio"]';

    private const DATA_URI_LIMIT = 20 * 1024;

    private const BLOCKED_PROPERTIES = ['behavior', '-moz-binding', 'binding'];

    private const BLOCKED_FUNCTIONS = ['src', 'image-set', '-webkit-image-set', 'image', 'element', '-moz-element', 'cross-fade', '-webkit-cross-fade', 'expression'];

    private string $css = '';

    private int $position = 0;

    /** @var array<string, true> */
    private array $removed = [];

    public function sanitize(string $css): CssSanitizeResult
    {
        $limit = SpecCatalogue::limit('css');

        if (strlen($css) > $limit) {
            return new CssSanitizeResult('', ['the stylesheet is larger than '.intdiv($limit, 1024).' KB; nothing was kept']);
        }

        $this->removed = [];
        $this->css = $this->normalize($css);
        $this->position = 0;

        $output = $this->serialize($this->rules('stylesheet'));

        return new CssSanitizeResult($output, array_keys($this->removed));
    }

    private function normalize(string $css): string
    {
        $css = str_replace("\0", '', $css);

        // Comments can hide anything; unterminated ones run to the end.
        $css = (string) preg_replace('#/\*.*?(?:\*/|$)#s', '', $css, -1, $count);

        if (str_contains($css, '<!--') || str_contains($css, '-->')) {
            $this->remove('HTML comment markers');
            $css = str_replace(['<!--', '-->'], '', $css);
        }

        return $css;
    }

    /**
     * Parses rules until the end of the input or the `}` closing the current block.
     *
     * @param  'stylesheet'|'group'|'keyframes'  $context
     * @return list<CssNode>
     */
    private function rules(string $context): array
    {
        $rules = [];

        while (true) {
            $this->skipWhitespace();

            if ($this->position >= strlen($this->css)) {
                return $rules;
            }

            if ($this->css[$this->position] === '}') {
                if ($context === 'stylesheet') {
                    $this->remove('an unmatched "}"');
                    $this->position++;

                    continue;
                }

                return $rules;
            }

            if ($context !== 'keyframes' && $this->css[$this->position] === '@') {
                $rule = $this->atRule();

                if ($rule !== null) {
                    $rules[] = $rule;
                }

                continue;
            }

            $prelude = trim($this->readUntil(['{', ';', '}']));
            $next = $this->peek();

            if ($next !== '{') {
                if ($prelude !== '') {
                    $this->remove('text outside a rule');
                }

                if ($next === ';') {
                    $this->position++;
                }

                continue;
            }

            $this->position++;
            $declarations = $this->declarations();
            $selector = $context === 'keyframes' ? $this->keyframeSelector($prelude) : $this->selector($prelude);

            if ($selector !== null && $declarations !== []) {
                $rules[] = new CssNode($selector, $declarations);
            }
        }
    }

    private function atRule(): ?CssNode
    {
        preg_match('/@([a-zA-Z-]*)/A', $this->css, $match, 0, $this->position);
        $name = strtolower($match[1] ?? '');
        $this->position += strlen($match[0] ?? '@');
        $prelude = trim($this->readUntil(['{', ';', '}']));
        $next = $this->peek();

        if ($next !== '{') {
            $this->remove("@{$name}");

            if ($next === ';') {
                $this->position++;
            }

            return null;
        }

        $this->position++;

        if (in_array($name, ['media', 'supports'], true)) {
            $children = $this->rules('group');
            $this->position++;

            if (preg_match('/^[a-zA-Z0-9\s(),:.\/%>=!-]+$/', $prelude) !== 1 || preg_match('/url\s*\(|\/\//i', $prelude) === 1) {
                $this->remove("@{$name} with an unsupported condition");

                return null;
            }

            $prelude = (string) preg_replace('/\s+/', ' ', $prelude);

            return $children === [] ? null : new CssNode("@{$name} {$prelude}", $children);
        }

        if ($name === 'keyframes') {
            $frames = $this->rules('keyframes');
            $this->position++;

            if (preg_match('/^-?[a-zA-Z_][a-zA-Z0-9_-]*$/', $prelude) !== 1) {
                $this->remove('@keyframes with an invalid name');

                return null;
            }

            return $frames === [] ? null : new CssNode("@keyframes {$prelude}", $frames);
        }

        $this->remove("@{$name}");
        $this->skipBlock();

        return null;
    }

    /**
     * Reads the declarations of a style rule up to its closing `}`.
     *
     * @return list<string>
     */
    private function declarations(): array
    {
        $declarations = [];

        while (true) {
            $raw = $this->readUntil([';', '{', '}']);
            $next = $this->peek();

            if ($next === '{') {
                $this->remove('nested rules');
                $this->position++;
                $this->skipBlock();

                continue;
            }

            if (trim($raw) !== '') {
                $declaration = $this->declaration($raw);

                if ($declaration !== null) {
                    $declarations[] = $declaration;
                }
            }

            if ($next === ';') {
                $this->position++;

                continue;
            }

            if ($next === '}') {
                $this->position++;
            }

            return $declarations;
        }
    }

    private function declaration(string $raw): ?string
    {
        [$property, $value] = array_pad(explode(':', $raw, 2), 2, null);
        $property = trim((string) $property);
        $value = trim((string) $value);

        if (! str_starts_with($property, '--')) {
            $property = strtolower($property);
        }

        if ($value === '' || preg_match('/^(?:--[a-zA-Z0-9_-]+|-?[a-z][a-z0-9-]*)$/', $property) !== 1) {
            $this->remove('an invalid declaration');

            return null;
        }

        if (in_array($property, self::BLOCKED_PROPERTIES, true)) {
            $this->remove("the {$property} property");

            return null;
        }

        $important = (bool) preg_match('/\s*!\s*important$/i', $value);
        $value = trim((string) preg_replace('/\s*!\s*important$/i', '', $value));

        $reason = $this->unsafeValue($value);

        if ($reason !== null) {
            $this->remove("{$property}: {$reason}");

            return null;
        }

        return "{$property}: {$value}".($important ? ' !important' : '');
    }

    /**
     * Why a value is not allowed, or null when it is safe.
     */
    private function unsafeValue(string $value): ?string
    {
        if ($value === '') {
            return 'an empty value';
        }

        // `;` is only possible inside brackets or strings here (declarations split on top-level `;`).
        if (preg_match('/[\\\\<>{}@]/', $value) === 1) {
            return 'escapes or markup characters';
        }

        $lower = strtolower($value);

        foreach (['javascript:', 'vbscript:', 'expression('] as $needle) {
            if (str_contains(str_replace(' ', '', $lower), $needle)) {
                return 'script';
            }
        }

        preg_match_all('/(-?[a-z][a-z0-9-]*)\s*\(/', $lower, $functions);

        foreach ($functions[1] as $function) {
            if (in_array($function, self::BLOCKED_FUNCTIONS, true)) {
                return "{$function}() is not allowed";
            }
        }

        preg_match_all('/url\s*\(\s*(["\']?)(.*?)\1\s*\)/is', $value, $urls, PREG_SET_ORDER);

        if (count($urls) !== substr_count($lower, 'url(') + substr_count($lower, 'url (')) {
            return 'a malformed url()';
        }

        foreach ($urls as [, , $url]) {
            if (preg_match('#^data:image/(?:png|jpeg|gif|webp|svg\+xml)[;,]#i', trim($url)) !== 1) {
                return 'url() may only hold an inline data: image';
            }

            if (strlen($url) > self::DATA_URI_LIMIT) {
                return 'an inline image larger than 20 KB';
            }
        }

        return null;
    }

    /**
     * Scopes every selector of a selector list to the studio template, or null when it is unsafe.
     */
    private function selector(string $prelude): ?string
    {
        $selectors = array_map('trim', $this->splitTopLevel($prelude, ','));

        foreach ($selectors as $selector) {
            if ($selector === '' || preg_match('/[\\\\<{}@;]/', $selector) === 1) {
                $this->remove('an invalid selector');

                return null;
            }
        }

        return implode(', ', array_map(fn (string $selector): string => $this->scope((string) preg_replace('/\s+/', ' ', $selector)), $selectors));
    }

    private function scope(string $selector): string
    {
        if (str_starts_with($selector, self::SCOPE)) {
            return $selector;
        }

        // The scope sits on <html>: `html`/`:root` become the scope itself, and attribute
        // selectors of the root (the theme) attach to it.
        if (preg_match('/^(?::root|html)(?=$|[\s\[:.#>+~])/i', $selector, $match) === 1) {
            return self::SCOPE.substr($selector, strlen($match[0]));
        }

        if (preg_match('/^\[data-theme/i', $selector) === 1) {
            return self::SCOPE.$selector;
        }

        return self::SCOPE.' '.$selector;
    }

    private function keyframeSelector(string $prelude): ?string
    {
        $frames = array_map('trim', explode(',', strtolower($prelude)));

        foreach ($frames as $frame) {
            if (preg_match('/^(?:from|to|\d{1,3}(?:\.\d+)?%)$/', $frame) !== 1) {
                $this->remove('an invalid keyframe selector');

                return null;
            }
        }

        return implode(', ', $frames);
    }

    /**
     * @param  list<CssNode>  $rules
     */
    private function serialize(array $rules, string $indent = ''): string
    {
        $output = '';

        foreach ($rules as $rule) {
            $output .= "{$indent}{$rule->prelude} {\n";

            foreach ($rule->body as $item) {
                $output .= $item instanceof CssNode
                    ? $this->serialize([$item], $indent.'  ')
                    : "{$indent}  {$item};\n";
            }

            $output .= "{$indent}}\n";
        }

        return str_replace('<', '', $output);
    }

    /**
     * Reads up to (not including) the first stop character outside strings and brackets.
     *
     * @param  list<string>  $stops
     */
    private function readUntil(array $stops): string
    {
        $start = $this->position;
        $depth = 0;
        $quote = null;
        $length = strlen($this->css);

        for (; $this->position < $length; $this->position++) {
            $char = $this->css[$this->position];

            if ($quote !== null) {
                if ($char === $quote) {
                    $quote = null;
                }

                continue;
            }

            if ($char === '"' || $char === "'") {
                $quote = $char;
            } elseif ($char === '(' || $char === '[') {
                $depth++;
            } elseif (($char === ')' || $char === ']') && $depth > 0) {
                $depth--;
            } elseif ($depth === 0 && in_array($char, $stops, true)) {
                break;
            }
        }

        return substr($this->css, $start, $this->position - $start);
    }

    /**
     * Skips to just after the `}` closing the block the position is in.
     */
    private function skipBlock(): void
    {
        $depth = 1;

        while ($this->position < strlen($this->css) && $depth > 0) {
            $this->readUntil(['{', '}']);
            $char = $this->peek();
            $depth += $char === '{' ? 1 : ($char === '}' ? -1 : 0);
            $this->position++;
        }
    }

    /**
     * @return list<string>
     */
    private function splitTopLevel(string $value, string $separator): array
    {
        $parts = [];
        $depth = 0;
        $quote = null;
        $current = '';

        foreach (str_split($value) as $char) {
            if ($quote !== null) {
                $quote = $char === $quote ? null : $quote;
            } elseif ($char === '"' || $char === "'") {
                $quote = $char;
            } elseif ($char === '(' || $char === '[') {
                $depth++;
            } elseif (($char === ')' || $char === ']') && $depth > 0) {
                $depth--;
            } elseif ($depth === 0 && $char === $separator) {
                $parts[] = $current;
                $current = '';

                continue;
            }

            $current .= $char;
        }

        $parts[] = $current;

        return $parts;
    }

    private function peek(): string
    {
        return $this->position < strlen($this->css) ? $this->css[$this->position] : '';
    }

    private function skipWhitespace(): void
    {
        $this->position += strspn($this->css, " \t\r\n\f", $this->position);
    }

    private function remove(string $what): void
    {
        $this->removed[$what] = true;
    }
}
