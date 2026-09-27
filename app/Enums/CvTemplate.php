<?php

namespace App\Enums;

use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

/**
 * The ATS-friendly CV templates (resources/views/cv, docs/tasks/phase-11-content-tools.md).
 */
enum CvTemplate: string implements HasDescription, HasLabel
{
    case Classic = 'classic';
    case Modern = 'modern';
    case Compact = 'compact';

    public function getLabel(): string
    {
        return match ($this) {
            self::Classic => 'Classic',
            self::Modern => 'Modern',
            self::Compact => 'Compact',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::Classic => 'Serif headings, centred header, thin rules. Traditional and safe everywhere.',
            self::Modern => 'Clean sans-serif with an accent colour for your name and headings.',
            self::Compact => 'Smaller type, inline skills and tighter spacing to fit more on each page.',
        };
    }

    /**
     * @return view-string
     */
    public function view(): string
    {
        return match ($this) {
            self::Classic => 'cv.classic',
            self::Modern => 'cv.modern',
            self::Compact => 'cv.compact',
        };
    }
}
