<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * The language an article is written in. It sets the `lang` and the reading direction of the article on the site.
 */
enum ArticleLanguage: string implements HasLabel
{
    case English = 'en';
    case Arabic = 'ar';

    public function getLabel(): string
    {
        return match ($this) {
            self::English => 'English',
            self::Arabic => 'العربية',
        };
    }

    /**
     * @return 'ltr'|'rtl'
     */
    public function direction(): string
    {
        return match ($this) {
            self::English => 'ltr',
            self::Arabic => 'rtl',
        };
    }
}
