<?php

namespace App\Support;

use Symfony\Component\Intl\Countries as IntlCountries;
use Symfony\Component\Intl\Exception\MissingResourceException;

/**
 * ISO-3166 alpha-2 country helpers (names in English).
 */
final class Countries
{
    /**
     * @return array<string, string> code => name, sorted by name
     */
    public static function options(): array
    {
        $names = IntlCountries::getNames('en');
        asort($names);

        return $names;
    }

    public static function name(?string $code): ?string
    {
        if (blank($code)) {
            return null;
        }

        try {
            return IntlCountries::getName(strtoupper($code), 'en');
        } catch (MissingResourceException) {
            return null;
        }
    }

    /**
     * Regional-indicator flag emoji for a country code ("DE" → 🇩🇪).
     */
    public static function flag(?string $code): string
    {
        if (blank($code) || strlen($code) !== 2) {
            return '';
        }

        return implode('', array_map(
            fn (string $letter): string => mb_chr(0x1F1E6 + ord($letter) - ord('A')),
            str_split(strtoupper($code)),
        ));
    }
}
