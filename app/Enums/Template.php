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
     * Google Fonts stylesheet loaded in the document head for this template.
     */
    public function fontsHref(): string
    {
        return match ($this) {
            self::Changelog => 'https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&family=Geist:wght@300..700&family=JetBrains+Mono:wght@400;500;600&display=swap',
            self::Playground => 'https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,100..900&family=Space+Grotesk:wght@300..700&family=Space+Mono:wght@400;700&display=swap',
            self::Terminal => 'https://fonts.googleapis.com/css2?family=DM+Mono:ital,wght@0,300;0,400;0,500;1,400&display=swap',
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
