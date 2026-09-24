<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Category of a project.
 */
enum ProjectCategory: string implements HasColor, HasLabel
{
    case Platform = 'platform';
    case Product = 'product';
    case OpenSource = 'open-source';
    case DesignSystem = 'design-system';

    public function getLabel(): string
    {
        return match ($this) {
            self::Platform => 'Platform',
            self::Product => 'Product',
            self::OpenSource => 'Open source',
            self::DesignSystem => 'Design system',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Platform => 'primary',
            self::Product => 'info',
            self::OpenSource => 'success',
            self::DesignSystem => 'warning',
        };
    }
}
