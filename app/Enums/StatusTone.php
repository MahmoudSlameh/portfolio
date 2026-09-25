<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Emphasis of a profile status entry.
 */
enum StatusTone: string implements HasColor, HasLabel
{
    case Signal = 'signal';
    case Neutral = 'neutral';

    public function getLabel(): string
    {
        return match ($this) {
            self::Signal => 'Signal',
            self::Neutral => 'Neutral',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Signal => 'success',
            self::Neutral => 'gray',
        };
    }
}
