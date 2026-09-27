<?php

namespace App\Support\Templates;

use Illuminate\Http\Request;

/**
 * A short-lived signature that lets a URL render one template without a session, e.g. for the
 * screenshot job's headless Chrome (P10-03). It signs the template id, theme and expiry only, so it
 * works on any page and whatever host Chrome uses to reach the site. (Laravel's relative URL
 * signatures do not validate on `/`, and absolute ones break when that host differs from APP_URL.)
 */
final class RenderSignature
{
    public const EXPIRES = '_expires';

    public const SIGNATURE = '_signature';

    /**
     * @return array<string, string|int> Query parameters for the URL
     */
    public static function sign(string $templateId, string $theme = 'light', int $minutes = 5): array
    {
        $expires = now()->addMinutes($minutes)->getTimestamp();

        return [
            TemplateManager::GALLERY_QUERY => $templateId,
            '_theme' => $theme,
            self::EXPIRES => $expires,
            self::SIGNATURE => self::hash($templateId, $theme, $expires),
        ];
    }

    public static function valid(Request $request): bool
    {
        $signature = $request->query(self::SIGNATURE);
        $expires = filter_var($request->query(self::EXPIRES), FILTER_VALIDATE_INT);

        if (! is_string($signature) || $expires === false || $expires < now()->getTimestamp()) {
            return false;
        }

        return hash_equals(self::hash((string) $request->query(TemplateManager::GALLERY_QUERY), (string) $request->query('_theme'), $expires), $signature);
    }

    private static function hash(string $templateId, string $theme, int $expires): string
    {
        return hash_hmac('sha256', "template-render|{$templateId}|{$theme}|{$expires}", (string) config('app.key'));
    }
}
