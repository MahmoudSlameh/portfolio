<?php

namespace App\Ai\Agents;

use App\Support\Studio\SpecCatalogue;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Ai\Attributes\CacheInstructions;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Laravel\Ai\Responses\AgentResponse;
use Laravel\Ai\Responses\StructuredAgentResponse;
use Stringable;

/**
 * Designs a studio template: returns a Template Spec (`studio/v1`) as a JSON string plus a short
 * summary (D31). The instructions are generated from SpecCatalogue, so they always describe what the
 * validator accepts and the engine renders. See docs/12-ai-templates.md §5.
 */
#[CacheInstructions]
class TemplateDesigner implements Agent, HasStructuredOutput
{
    use Promptable;

    /**
     * CSS variables StudioStyles defines, as documented to the model (a test checks they exist).
     *
     * @var array<string, string>
     */
    public const CSS_VARIABLES = [
        '--paper' => '(bg)',
        '--surface' => '(surface)',
        '--ink' => '(text)',
        '--ink-muted' => '(muted)',
        '--signal' => '(accent)',
        '--line' => '(border)',
        '--st-radius' => '(radius)',
        '--st-shadow' => '(shadow)',
        '--tpl-font-display' => '(display font)',
        '--tpl-font-sans' => '(body font)',
        '--tpl-font-mono' => '(mono font)',
    ];

    public function instructions(): Stringable|string
    {
        return self::guide();
    }

    /**
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'spec' => $schema->string()->description('The complete Template Spec as a JSON document (an object serialised to a string).')->required(),
            'summary' => $schema->string()->description('One or two sentences describing the design, for the owner.')->required(),
        ];
    }

    public function maxTokens(): int
    {
        return (int) config('studio.ai.max_output_tokens', 16000);
    }

    public function timeout(): int
    {
        return (int) config('studio.ai.timeout', 240);
    }

    /**
     * The message for a repair turn: what the validator rejected and what the sanitiser removed.
     *
     * @param  list<string>  $errors
     * @param  list<string>  $cssRemoved
     */
    public static function repairPrompt(array $errors, array $cssRemoved = []): string
    {
        $parts = ['Your spec was rejected. Fix every problem below and return the complete corrected spec (not a diff).'];

        if ($errors !== []) {
            $parts[] = "Validation errors (path, then reason):\n- ".implode("\n- ", $errors);
        }

        if ($cssRemoved !== []) {
            $parts[] = "CSS that was removed for safety (drop it or rewrite it within the rules):\n- ".implode("\n- ", $cssRemoved);
        }

        return implode("\n\n", $parts);
    }

    /**
     * The `spec` string from a response, without a Markdown code fence if the model added one.
     * Validate it with SpecValidator::validateJson().
     */
    public static function specFrom(AgentResponse $response): string
    {
        $spec = self::structured($response)['spec'] ?? '';

        if (is_array($spec)) {
            // Some providers return the object itself instead of a string.
            return (string) json_encode($spec, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        $spec = trim(is_string($spec) ? $spec : '');

        if (preg_match('/^```(?:json)?\s*(.*?)\s*```$/s', $spec, $match) === 1) {
            return $match[1];
        }

        return $spec;
    }

    public static function summaryFrom(AgentResponse $response): string
    {
        $summary = self::structured($response)['summary'] ?? '';

        return is_string($summary) ? trim($summary) : '';
    }

    /**
     * The structured output; when a provider answered in plain text, its text parsed as JSON.
     *
     * @return array<array-key, mixed>
     */
    private static function structured(AgentResponse $response): array
    {
        if ($response instanceof StructuredAgentResponse) {
            return $response->structured;
        }

        $decoded = json_decode($response->text, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * The system instructions, built from the catalogue.
     */
    public static function guide(): string
    {
        $fonts = SpecCatalogue::fonts();
        $fontList = fn (string $role): string => implode(', ', array_map(
            fn (string $id): string => "`{$id}` ({$fonts[$id]['label']})",
            SpecCatalogue::fontsFor($role),
        ));

        $sections = [];

        foreach (SpecCatalogue::HOME_SECTIONS as $section => $rule) {
            $props = array_map(
                fn (string $name, array $prop): string => $prop['type'] === 'integer' ? "`{$name}` integer {$prop['min']}–{$prop['max']}" : "`{$name}` boolean",
                array_keys($rule['props']),
                $rule['props'],
            );
            $sections[] = "- `{$section}`: variants ".self::codeList($rule['variants']).($props === [] ? '; no props' : '; props '.implode(', ', $props));
        }

        $pages = array_map(
            fn (string $page, array $variants): string => "- `{$page}`: ".self::codeList($variants),
            array_keys(SpecCatalogue::PAGES),
            SpecCatalogue::PAGES,
        );

        $copy = array_map(fn (string $key, int $max): string => "`{$key}` (≤ {$max})", array_keys(SpecCatalogue::COPY), SpecCatalogue::COPY);

        $tokens = array_map(fn (string $token, array $values): string => "- `{$token}`: ".self::codeList($values), array_keys(SpecCatalogue::TOKENS), SpecCatalogue::TOKENS);

        $example = json_encode(SpecCatalogue::example(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $version = SpecCatalogue::VERSION;
        $nameMax = SpecCatalogue::limit('name');
        $sectionsMax = SpecCatalogue::limit('home_sections');
        $cssMax = intdiv(SpecCatalogue::limit('css'), 1024);
        $roles = self::codeList(SpecCatalogue::COLOR_ROLES);
        $headers = self::codeList(SpecCatalogue::LAYOUT['header']);
        $footers = self::codeList(SpecCatalogue::LAYOUT['footer']);
        $containers = self::codeList(SpecCatalogue::CONTAINERS);
        $hooks = implode(' ', array_map(fn (string $hook): string => ".{$hook}", SpecCatalogue::CLASS_HOOKS));
        $sectionList = implode("\n", $sections);
        $pageList = implode("\n", $pages);
        $tokenList = implode("\n", $tokens);
        $copyList = implode(', ', $copy);
        $variables = implode(', ', array_map(fn (string $name, string $role): string => "`var({$name})` {$role}", array_keys(self::CSS_VARIABLES), self::CSS_VARIABLES));

        return <<<GUIDE
            You are a senior web designer. You design templates for a personal portfolio site by writing a
            Template Spec: a JSON document that a fixed rendering engine turns into every page of the site.
            You never write HTML, JavaScript or React. The site's content (name, bio, projects, articles,
            images, links) comes from the owner's database; the spec only decides how it looks.

            # Output

            Return an object with two strings:
            - `spec`: the complete Template Spec, serialised as JSON.
            - `summary`: one or two sentences for the owner about the design.

            # The spec (`{$version}`)

            Top-level keys, nothing else: `\$schema` (always "{$version}"), `name` (≤ {$nameMax} characters),
            `tokens`, `layout`, `pages`, and optionally `copy` and `css`.

            ## tokens
            - `colors.light` and `colors.dark`: each has exactly the roles {$roles}, as `#rrggbb` hex.
              `bg` is the page, `surface` cards and panels, `text` body text, `muted` secondary text,
              `accent` links, buttons and highlights, `border` lines. Text and muted text must reach WCAG AA
              contrast (4.5:1) on `bg` and `surface` in both themes; the dark theme is a real dark design,
              not an inversion.
            - `fonts`: `display` (headings) one of {$fontList('display')}; `body` one of {$fontList('body')};
              `mono` one of {$fontList('mono')}. No other fonts exist.
            {$tokenList}

            ## layout
            - `header.variant`: {$headers}
            - `footer.variant`: {$footers}
            - `container` (optional): {$containers}

            ## pages
            `pages.home` is an ordered list (1–{$sectionsMax}, usually 6–10) of `{"section", "variant", "props"?}`;
            each section at most once. `props` only where listed. Sections with no content are hidden
            automatically, so include the ones that fit the design.
            {$sectionList}

            Every other page is `{"variant": "…"}` and all of them are required:
            {$pageList}

            ## copy (optional)
            Short UI texts that replace the defaults, plain text only, never personal facts:
            {$copyList}.

            ## css (optional, ≤ {$cssMax} KB)
            Extra CSS for details the tokens cannot express (letter-spacing, borders, hover states,
            decorative backgrounds). Target these stable classes (sections also get `.st-<section>`):
            {$hooks}
            Rules: selectors are scoped to the template automatically; `[data-theme="dark"]` targets dark
            mode. Prefer the theme's variables over new colours: {$variables}. Not allowed (removed): `@import`, `@font-face`, any other at-rule except `@media`,
            `@supports` and `@keyframes`; `url()` except small inline `data:` images; `expression()`,
            `javascript:`, backslash escapes, `<` or `>`. Keep it short; prefer tokens and variants.

            # Reference images
            When images are attached, derive the palette, type personality, density, radius, shadow and
            layout from them. Never copy their text, names or logos.

            # Example of a valid spec
            {$example}
            GUIDE;
    }

    /**
     * @param  list<string>  $values
     */
    private static function codeList(array $values): string
    {
        return implode(', ', array_map(fn (string $value): string => "`{$value}`", $values));
    }
}
