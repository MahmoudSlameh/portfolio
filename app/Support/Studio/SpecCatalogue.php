<?php

namespace App\Support\Studio;

/**
 * Everything a Template Spec (`studio/v1`) may contain: the single source of truth for the
 * validator, the JSON Schema given to the AI and the React engine's catalogue.
 *
 * `php artisan studio:generate` writes resources/studio/schema/v1.json and
 * resources/js/templates/studio/catalogue.ts from this class; a test fails when they are stale.
 * Adding a section or variant here means implementing it in the engine (P8-04).
 *
 * @phpstan-type PropRule array{type: 'boolean'}|array{type: 'integer', min: int, max: int}
 * @phpstan-type SectionRule array{variants: list<string>, props: array<string, PropRule>}
 */
final class SpecCatalogue
{
    public const VERSION = 'studio/v1';

    /** Colour roles of each theme (light and dark). */
    public const COLOR_ROLES = ['bg', 'surface', 'text', 'muted', 'accent', 'border'];

    /** Font roles and the font `kinds` (config/studio.php) allowed in each. */
    public const FONT_ROLES = ['display' => 'display', 'body' => 'body', 'mono' => 'mono'];

    /** @var array<string, list<string>> */
    public const TOKENS = [
        'radius' => ['none', 'sm', 'md', 'lg', 'full'],
        'density' => ['compact', 'comfortable', 'airy'],
        'shadow' => ['none', 'soft', 'hard'],
        'motion' => ['none', 'subtle', 'lively'],
    ];

    /** @var array<string, list<string>> */
    public const LAYOUT = [
        'header' => ['bar-sticky', 'floating-pill', 'sidebar', 'minimal'],
        'footer' => ['minimal', 'columns', 'big-name'],
    ];

    /** @var list<string> */
    public const CONTAINERS = ['narrow', 'default', 'wide'];

    /**
     * Home page sections, in no particular order (the spec orders them).
     *
     * @var array<string, SectionRule>
     */
    public const HOME_SECTIONS = [
        'hero' => [
            'variants' => ['centered', 'split-portrait', 'editorial', 'terminal'],
            'props' => ['showAvailability' => ['type' => 'boolean'], 'showSocials' => ['type' => 'boolean']],
        ],
        'about' => ['variants' => ['prose', 'columns'], 'props' => []],
        'stats' => ['variants' => ['inline', 'cards'], 'props' => []],
        'skills' => ['variants' => ['chips', 'grid', 'bars'], 'props' => []],
        'career' => [
            'variants' => ['timeline', 'cards', 'table'],
            'props' => ['limit' => ['type' => 'integer', 'min' => 1, 'max' => 20]],
        ],
        'projects' => [
            'variants' => ['grid', 'bento', 'list', 'slider'],
            'props' => ['limit' => ['type' => 'integer', 'min' => 1, 'max' => 12]],
        ],
        'clients' => ['variants' => ['logos', 'marquee'], 'props' => []],
        'testimonials' => ['variants' => ['carousel', 'wall'], 'props' => []],
        'education' => ['variants' => ['list', 'cards'], 'props' => []],
        'writing' => [
            'variants' => ['list', 'cards'],
            'props' => ['limit' => ['type' => 'integer', 'min' => 1, 'max' => 12]],
        ],
        'books' => [
            'variants' => ['shelf', 'covers'],
            'props' => ['limit' => ['type' => 'integer', 'min' => 1, 'max' => 24]],
        ],
        'contact' => ['variants' => ['card', 'split', 'minimal'], 'props' => []],
    ];

    /**
     * Every other page: one variant each.
     *
     * @var array<string, list<string>>
     */
    public const PAGES = [
        'projects' => ['grid', 'list', 'table'],
        'caseStudy' => ['longform', 'sidebar'],
        'writing' => ['list', 'cards'],
        'article' => ['centered', 'wide'],
        'books' => ['shelf', 'grid'],
        'uses' => ['columns', 'list'],
        'now' => ['notes', 'timeline'],
        'notFound' => ['big-number', 'minimal'],
    ];

    /**
     * Stable class names of the engine that a spec's CSS may target (all under the
     * `[data-template="studio"]` scope). Home sections also get `.st-<section>`, e.g. `.st-career`.
     * A test checks every hook exists in the engine.
     *
     * @var list<string>
     */
    public const CLASS_HOOKS = [
        // Layout
        'st-root', 'st-header', 'st-header--bar-sticky', 'st-header--floating-pill', 'st-header--sidebar', 'st-header--minimal',
        'st-brand', 'st-nav', 'st-tools', 'st-socials', 'st-main', 'st-footer', 'st-footer--minimal', 'st-footer--columns', 'st-footer--big-name',
        // Building blocks
        'st-container', 'st-section', 'st-kicker', 'st-section-title', 'st-card', 'st-button', 'st-button--ghost', 'st-chip', 'st-link', 'st-field', 'st-empty',
        // Home
        'st-hero', 'st-hero--centered', 'st-hero--split-portrait', 'st-hero--editorial', 'st-hero--terminal', 'st-hero__title', 'st-hero__actions',
        'st-hero__socials', 'st-hero__portrait', 'st-availability', 'st-career__highlights', 'st-project-card', 'st-project-row', 'st-slider',
        'st-marquee__track', 'st-logo', 'st-testimonial', 'st-article-card', 'st-article-row', 'st-book-cover', 'st-contact-form',
        'st-contact-success', 'st-contact__email',
        // Pages
        'st-page-header', 'st-filters', 'st-case-study', 'st-case-block', 'st-case-meta', 'st-article', 'st-article-body', 'st-toc',
        'st-code', 'st-quote', 'st-callout', 'st-adjacent', 'st-not-found',
    ];

    /**
     * UI micro-copy a spec may override, with maximum lengths. Content (names, bios, projects…)
     * never lives in a spec: it always comes from the panel (hard rule 1).
     *
     * @var array<string, int>
     */
    public const COPY = [
        'heroKicker' => 60,
        'heroCta' => 30,
        'aboutHeading' => 60,
        'skillsHeading' => 60,
        'careerHeading' => 60,
        'projectsHeading' => 60,
        'clientsHeading' => 60,
        'testimonialsHeading' => 60,
        'educationHeading' => 60,
        'writingHeading' => 60,
        'booksHeading' => 60,
        'contactHeading' => 80,
        'contactIntro' => 200,
        'footerNote' => 120,
    ];

    /**
     * Fonts allowed in a spec, from config/studio.php.
     *
     * @return array<string, array{label: string, family: string, kinds: list<string>, preload: string|null}>
     */
    public static function fonts(): array
    {
        /** @var array<string, array{label: string, family: string, kinds: list<string>, preload: string|null}> */
        return config('studio.fonts', []);
    }

    /**
     * @return list<string> ids of the fonts that can fill a role (display, body, mono)
     */
    public static function fontsFor(string $role): array
    {
        $kind = self::FONT_ROLES[$role] ?? $role;

        return array_keys(array_filter(self::fonts(), fn (array $font): bool => in_array($kind, $font['kinds'], true)));
    }

    /**
     * The example spec shipped with the app (resources/studio/examples): a valid starting point for
     * a new template, the factories and the tests.
     *
     * @return array<string, mixed>
     */
    public static function example(): array
    {
        return self::examples()['neon-brutalist'];
    }

    /**
     * Every example spec, keyed by file name. Together they use every section and variant.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function examples(): array
    {
        $examples = [];

        foreach (glob(resource_path('studio/examples/*.json')) ?: [] as $file) {
            /** @var array<string, mixed> $spec */
            $spec = json_decode((string) file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
            $examples[basename($file, '.json')] = $spec;
        }

        ksort($examples);

        return $examples;
    }

    public static function limit(string $key): int
    {
        return (int) config("studio.limits.{$key}");
    }

    /**
     * The catalogue as plain data, for the React engine and the AI instructions.
     *
     * @return array<string, mixed>
     */
    public static function toArray(): array
    {
        return [
            'version' => self::VERSION,
            'colorRoles' => self::COLOR_ROLES,
            'fonts' => array_map(fn (array $font): array => [
                'label' => $font['label'],
                'family' => $font['family'],
                'kinds' => $font['kinds'],
            ], self::fonts()),
            'tokens' => self::TOKENS,
            'layout' => self::LAYOUT,
            'containers' => self::CONTAINERS,
            // Sections without props become {} (not []) so the engine always gets an object.
            'homeSections' => array_map(fn (array $rule): array => [...$rule, 'props' => (object) $rule['props']], self::HOME_SECTIONS),
            'pages' => self::PAGES,
            'copy' => self::COPY,
            'classHooks' => self::CLASS_HOOKS,
            'limits' => [
                'name' => self::limit('name'),
                'homeSections' => self::limit('home_sections'),
                'css' => self::limit('css'),
            ],
        ];
    }
}
