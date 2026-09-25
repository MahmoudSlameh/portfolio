<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

/**
 * Contract type of a work experience.
 */
enum EmploymentType: string implements HasColor, HasIcon, HasLabel
{
    case FullTime = 'full-time';
    case PartTime = 'part-time';
    case Contract = 'contract';
    case Freelance = 'freelance';
    case OpenSource = 'open-source';
    case Internship = 'internship';

    public function getLabel(): string
    {
        return match ($this) {
            self::FullTime => 'Full-time',
            self::PartTime => 'Part-time',
            self::Contract => 'Contract',
            self::Freelance => 'Freelance',
            self::OpenSource => 'Open source',
            self::Internship => 'Internship',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::FullTime => 'primary',
            self::PartTime => 'info',
            self::Contract => 'warning',
            self::Freelance => 'success',
            self::OpenSource => 'gray',
            self::Internship => 'info',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::FullTime => Heroicon::OutlinedBriefcase,
            self::PartTime => Heroicon::OutlinedClock,
            self::Contract => Heroicon::OutlinedDocumentText,
            self::Freelance => Heroicon::OutlinedSparkles,
            self::OpenSource => Heroicon::OutlinedCodeBracket,
            self::Internship => Heroicon::OutlinedAcademicCap,
        };
    }
}
