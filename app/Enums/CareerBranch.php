<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Git-graph branch used by the Changelog template career section.
 */
enum CareerBranch: string implements HasLabel
{
    case Main = 'main';
    case Freelance = 'freelance';
    case Oss = 'oss';

    public function getLabel(): string
    {
        return match ($this) {
            self::Main => 'main',
            self::Freelance => 'freelance',
            self::Oss => 'oss',
        };
    }

    /**
     * Branch a role sits on when no explicit branch was chosen.
     */
    public static function forEmploymentType(EmploymentType $type): self
    {
        return match ($type) {
            EmploymentType::OpenSource => self::Oss,
            EmploymentType::Freelance, EmploymentType::Contract => self::Freelance,
            default => self::Main,
        };
    }
}
