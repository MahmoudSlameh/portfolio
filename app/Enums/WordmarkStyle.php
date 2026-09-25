<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Typographic fallback used when a company has no logo.
 */
enum WordmarkStyle: string implements HasLabel
{
    case Serif = 'serif';
    case SerifItalic = 'serif-italic';
    case Mono = 'mono';
    case SansBold = 'sans-bold';
    case SansLight = 'sans-light';
    case Spaced = 'spaced';

    public function getLabel(): string
    {
        return match ($this) {
            self::Serif => 'Serif',
            self::SerifItalic => 'Serif italic',
            self::Mono => 'Monospace',
            self::SansBold => 'Sans bold',
            self::SansLight => 'Sans light',
            self::Spaced => 'Letter-spaced',
        };
    }
}
