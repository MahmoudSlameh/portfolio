<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

/**
 * Lifecycle status of a project.
 */
enum ProjectStatus: string implements HasColor, HasIcon, HasLabel
{
    case Live = 'live';
    case Maintained = 'maintained';
    case Archived = 'archived';
    case InProgress = 'in-progress';

    public function getLabel(): string
    {
        return match ($this) {
            self::Live => 'Live',
            self::Maintained => 'Maintained',
            self::Archived => 'Archived',
            self::InProgress => 'In progress',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Live => 'success',
            self::Maintained => 'info',
            self::Archived => 'gray',
            self::InProgress => 'warning',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Live => Heroicon::OutlinedSignal,
            self::Maintained => Heroicon::OutlinedWrenchScrewdriver,
            self::Archived => Heroicon::OutlinedArchiveBox,
            self::InProgress => Heroicon::OutlinedArrowPath,
        };
    }
}
