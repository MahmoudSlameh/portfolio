<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Pattern used for generated book covers.
 */
enum BookCoverStyle: string implements HasLabel
{
    case Band = 'band';
    case Block = 'block';
    case Rule = 'rule';
    case Circle = 'circle';
    case Split = 'split';

    public function getLabel(): string
    {
        return match ($this) {
            self::Band => 'Band',
            self::Block => 'Block',
            self::Rule => 'Rule',
            self::Circle => 'Circle',
            self::Split => 'Split',
        };
    }
}
