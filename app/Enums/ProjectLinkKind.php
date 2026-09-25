<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

/**
 * Kind of an external project link.
 */
enum ProjectLinkKind: string implements HasIcon, HasLabel
{
    case Live = 'live';
    case Source = 'source';
    case Writeup = 'writeup';
    case Talk = 'talk';

    public function getLabel(): string
    {
        return match ($this) {
            self::Live => 'Live site',
            self::Source => 'Source code',
            self::Writeup => 'Write-up',
            self::Talk => 'Talk',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Live => Heroicon::OutlinedGlobeAlt,
            self::Source => Heroicon::OutlinedCodeBracket,
            self::Writeup => Heroicon::OutlinedDocumentText,
            self::Talk => Heroicon::OutlinedMicrophone,
        };
    }
}
