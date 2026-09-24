<?php

namespace App\Enums;

use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

/**
 * Public site templates. The value is also the Inertia page namespace
 * (e.g. "terminal/Home") and the `data-template` attribute on <html>.
 */
enum Template: string implements HasDescription, HasLabel
{
    case Changelog = 'changelog';
    case Playground = 'playground';
    case Terminal = 'terminal';

    public function getLabel(): string
    {
        return match ($this) {
            self::Changelog => 'Changelog',
            self::Playground => 'Playground',
            self::Terminal => 'Terminal',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::Changelog => 'Editorial “engineer’s changelog”: a git-graph career, versions and release notes.',
            self::Playground => 'Playful bento layout with stickers, career tickets and a floating dock.',
            self::Terminal => 'Dark developer-terminal look with mono type, a project slider and services.',
        };
    }

    /**
     * Above-the-fold font files (Vite manifest keys) preloaded in the document head
     * so the first paint already uses the template's type and the swap causes no layout shift.
     *
     * @return list<string>
     */
    public function preloadFonts(): array
    {
        return match ($this) {
            self::Changelog => [
                'node_modules/@fontsource-variable/geist/files/geist-latin-wght-normal.woff2',
                'node_modules/@fontsource-variable/bricolage-grotesque/files/bricolage-grotesque-latin-opsz-normal.woff2',
            ],
            self::Playground => [
                'node_modules/@fontsource-variable/space-grotesk/files/space-grotesk-latin-wght-normal.woff2',
                'node_modules/@fontsource-variable/archivo/files/archivo-latin-wdth-normal.woff2',
            ],
            self::Terminal => [
                'node_modules/@fontsource/dm-mono/files/dm-mono-latin-400-normal.woff2',
                'node_modules/@fontsource/dm-mono/files/dm-mono-latin-500-normal.woff2',
            ],
        };
    }

    /**
     * Public path of the screenshot shown on the admin Appearance page.
     */
    public function screenshot(): string
    {
        return "templates/{$this->value}.webp";
    }

    public static function default(): self
    {
        return self::Changelog;
    }
}
