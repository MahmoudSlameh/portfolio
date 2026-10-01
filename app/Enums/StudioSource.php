<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

/**
 * How a studio template was made.
 */
enum StudioSource: string implements HasIcon, HasLabel
{
    case Ai = 'ai';
    case Manual = 'manual';
    case Import = 'import';

    public function getLabel(): string
    {
        return match ($this) {
            self::Ai => 'Generated with AI',
            self::Manual => 'Written by hand',
            self::Import => 'Imported',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Ai => Heroicon::OutlinedSparkles,
            self::Manual => Heroicon::OutlinedCodeBracket,
            self::Import => Heroicon::OutlinedArrowDownTray,
        };
    }
}
