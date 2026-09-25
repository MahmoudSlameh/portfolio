<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

/**
 * Availability shown on the public profile.
 */
enum AvailabilityStatus: string implements HasColor, HasIcon, HasLabel
{
    case Open = 'open';
    case Limited = 'limited';
    case Closed = 'closed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Open => 'Open to work',
            self::Limited => 'Limited availability',
            self::Closed => 'Not available',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Open => 'success',
            self::Limited => 'warning',
            self::Closed => 'danger',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Open => Heroicon::OutlinedCheckCircle,
            self::Limited => Heroicon::OutlinedClock,
            self::Closed => Heroicon::OutlinedXCircle,
        };
    }
}
