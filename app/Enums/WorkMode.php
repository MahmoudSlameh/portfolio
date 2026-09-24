<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

/**
 * Where the work happens.
 */
enum WorkMode: string implements HasColor, HasIcon, HasLabel
{
    case OnSite = 'on-site';
    case Remote = 'remote';
    case Hybrid = 'hybrid';

    public function getLabel(): string
    {
        return match ($this) {
            self::OnSite => 'On-site',
            self::Remote => 'Remote',
            self::Hybrid => 'Hybrid',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::OnSite => 'info',
            self::Remote => 'success',
            self::Hybrid => 'warning',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::OnSite => Heroicon::OutlinedBuildingOffice,
            self::Remote => Heroicon::OutlinedGlobeAlt,
            self::Hybrid => Heroicon::OutlinedArrowsRightLeft,
        };
    }
}
