<?php

namespace App\Mcp\Support;

use Laravel\Passport\Passport;

/**
 * Checks the Passport keys the Claude connector signs and verifies tokens with. A key Passport cannot
 * use makes every request to /mcp fail with a 500 (even the first one, without a token), so Claude
 * cannot even start signing in.
 */
final class OAuthKeys
{
    /**
     * Permissions league/oauth2-server accepts for a key file (anything else raises an error).
     */
    private const ALLOWED_PERMISSIONS = ['400', '440', '600', '640', '660'];

    /**
     * What is wrong with the keys, as problem title => how to fix it. Empty when they are usable.
     *
     * @return array<string, string>
     */
    public static function problems(): array
    {
        $files = array_filter([
            'private' => blank(config('passport.private_key')) ? Passport::keyPath('oauth-private.key') : null,
            'public' => blank(config('passport.public_key')) ? Passport::keyPath('oauth-public.key') : null,
        ]);

        $missing = array_filter($files, fn (string $path): bool => ! is_file($path));

        if ($missing !== []) {
            return ['OAuth keys are missing' => 'Run "php artisan passport:keys" on the server (or set PASSPORT_PRIVATE_KEY and PASSPORT_PUBLIC_KEY), otherwise Claude cannot sign in.'];
        }

        $problems = [];
        $unreadable = array_filter($files, fn (string $path): bool => ! is_readable($path));

        if ($unreadable !== []) {
            $problems['The web server cannot read the OAuth keys'] = 'Give the keys to the user PHP runs as, e.g. "chown www-data:www-data '.self::list($unreadable).'". Until then every request to /mcp fails with a 500.';
        }

        $wrongPermissions = Passport::$validateKeyPermissions && PHP_OS_FAMILY !== 'Windows'
            ? array_filter($files, fn (string $path): bool => ! in_array(decoct((int) fileperms($path) & 0777), self::ALLOWED_PERMISSIONS, true))
            : [];

        if ($wrongPermissions !== []) {
            $problems['The OAuth key permissions are too open'] = 'Passport only accepts key files readable by their owner (and group): run "chmod 600 '.self::list($wrongPermissions).'". Until then every request to /mcp fails with a 500.';
        }

        return $problems;
    }

    /**
     * @param  array<string, string>  $paths
     */
    private static function list(array $paths): string
    {
        return implode(' ', array_map(
            fn (string $path): string => str_starts_with($path, base_path().DIRECTORY_SEPARATOR) ? substr($path, strlen(base_path()) + 1) : $path,
            array_values($paths),
        ));
    }
}
