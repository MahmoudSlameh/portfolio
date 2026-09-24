<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Topic chosen on the contact form.
 */
enum ContactTopic: string implements HasColor, HasLabel
{
    case Role = 'role';
    case Advisory = 'advisory';
    case Speaking = 'speaking';
    case Hello = 'hello';

    public function getLabel(): string
    {
        return match ($this) {
            self::Role => 'Role / job offer',
            self::Advisory => 'Advisory',
            self::Speaking => 'Speaking',
            self::Hello => 'Just saying hello',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Role => 'primary',
            self::Advisory => 'info',
            self::Speaking => 'warning',
            self::Hello => 'gray',
        };
    }
}
