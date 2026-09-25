<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

/**
 * Group kind on the Uses page.
 */
enum UsesKind: string implements HasIcon, HasLabel
{
    case Hardware = 'hardware';
    case Software = 'software';
    case Development = 'development';

    public function getLabel(): string
    {
        return match ($this) {
            self::Hardware => 'Hardware',
            self::Software => 'Software',
            self::Development => 'Development',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Hardware => Heroicon::OutlinedComputerDesktop,
            self::Software => Heroicon::OutlinedSquares2x2,
            self::Development => Heroicon::OutlinedCommandLine,
        };
    }
}
