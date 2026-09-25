<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

/**
 * Reading status of a book.
 */
enum ReadingStatus: string implements HasColor, HasIcon, HasLabel
{
    case Reading = 'reading';
    case Read = 'read';
    case ToRead = 'to-read';

    public function getLabel(): string
    {
        return match ($this) {
            self::Reading => 'Reading',
            self::Read => 'Read',
            self::ToRead => 'To read',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Reading => 'warning',
            self::Read => 'success',
            self::ToRead => 'gray',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Reading => Heroicon::OutlinedBookOpen,
            self::Read => Heroicon::OutlinedCheckCircle,
            self::ToRead => Heroicon::OutlinedBookmark,
        };
    }
}
