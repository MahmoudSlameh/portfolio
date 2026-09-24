<?php

namespace App\Support\Media;

use Illuminate\Support\Str;

/**
 * Inline SVG avatar with initials, used when a record has no image (no external avatar service).
 */
final class InitialsAvatar
{
    public static function dataUri(string $name): string
    {
        $initials = e(Str::of($name)->explode(' ')->filter()->take(2)->map(fn (string $word): string => Str::upper(Str::substr($word, 0, 1)))->implode(''));

        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" rx="12" fill="#e0e7ff"/><text x="50%" y="50%" dy=".35em" text-anchor="middle" font-family="Inter,system-ui,sans-serif" font-size="24" font-weight="600" fill="#3730a3">{$initials}</text></svg>
        SVG;

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
