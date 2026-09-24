<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

/**
 * Relationship with a company.
 */
enum CompanyKind: string implements HasColor, HasIcon, HasLabel
{
    case Employer = 'employer';
    case Client = 'client';

    public function getLabel(): string
    {
        return match ($this) {
            self::Employer => 'Employer',
            self::Client => 'Client',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Employer => 'info',
            self::Client => 'success',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Employer => Heroicon::OutlinedBuildingOffice2,
            self::Client => Heroicon::OutlinedHandThumbUp,
        };
    }
}
