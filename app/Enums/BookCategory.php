<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Category of a book.
 */
enum BookCategory: string implements HasLabel
{
    case Engineering = 'engineering';
    case Design = 'design';
    case Systems = 'systems';
    case Fiction = 'fiction';
    case History = 'history';
    case Philosophy = 'philosophy';

    public function getLabel(): string
    {
        return match ($this) {
            self::Engineering => 'Engineering',
            self::Design => 'Design',
            self::Systems => 'Systems',
            self::Fiction => 'Fiction',
            self::History => 'History',
            self::Philosophy => 'Philosophy',
        };
    }
}
