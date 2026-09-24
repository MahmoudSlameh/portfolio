<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Social / contact platform (maps to the frontend brand icon).
 */
enum SocialPlatform: string implements HasLabel
{
    case Github = 'github';
    case Linkedin = 'linkedin';
    case X = 'x';
    case Mastodon = 'mastodon';
    case Rss = 'rss';
    case ReadCv = 'read-cv';
    case Youtube = 'youtube';
    case Dribbble = 'dribbble';
    case StackOverflow = 'stackoverflow';
    case Email = 'email';
    case Website = 'website';

    public function getLabel(): string
    {
        return match ($this) {
            self::Github => 'GitHub',
            self::Linkedin => 'LinkedIn',
            self::X => 'X',
            self::Mastodon => 'Mastodon',
            self::Rss => 'RSS',
            self::ReadCv => 'Read.cv',
            self::Youtube => 'YouTube',
            self::Dribbble => 'Dribbble',
            self::StackOverflow => 'Stack Overflow',
            self::Email => 'Email',
            self::Website => 'Website',
        };
    }
}
