<?php

namespace App\Support\Content;

use App\Support\Countries;

/**
 * Human-readable location labels.
 */
final class Location
{
    /**
     * "Berlin, Germany", optionally followed by a qualifier: "Berlin, Germany · Remote". Empty parts are skipped.
     */
    public static function label(?string $city, ?string $countryCode, ?string $qualifier = null): ?string
    {
        $place = collect([$city, Countries::name($countryCode)])->filter()->implode(', ');
        $label = collect([$place, $qualifier])->filter()->implode(' · ');

        return $label === '' ? null : $label;
    }
}
