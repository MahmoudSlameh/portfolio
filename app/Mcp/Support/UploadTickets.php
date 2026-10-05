<?php

namespace App\Mcp\Support;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Single-use upload tickets for request_image_upload: the tool issues a random token, the agent PUTs the
 * file to /mcp/uploads/{token}. Tickets live in the cache under a hash of the token (so the cache never
 * holds a usable token), expire after `portfolio.mcp.uploads.minutes` and are deleted once used.
 *
 * @phpstan-type Ticket array{target: string, project_id: int|null, company_id: int|null, variant: string, mime_type: string, filename: string, alt: string, caption: string|null, user_id: int|string|null, expires_at: string}
 */
final class UploadTickets
{
    /**
     * @param  array{target: string, project_id?: int|null, company_id?: int|null, variant?: string, mime_type: string, filename: string, alt: string, caption?: string|null, user_id?: int|string|null}  $data
     * @return array{token: string, expires_at: CarbonImmutable}
     */
    public static function issue(array $data): array
    {
        $token = Str::random(64);
        $expiresAt = CarbonImmutable::now()->addMinutes(self::minutes());

        Cache::put(self::key($token), [
            'target' => $data['target'],
            'project_id' => $data['project_id'] ?? null,
            'company_id' => $data['company_id'] ?? null,
            'variant' => $data['variant'] ?? 'light',
            'mime_type' => $data['mime_type'],
            'filename' => $data['filename'],
            'alt' => $data['alt'],
            'caption' => $data['caption'] ?? null,
            'user_id' => $data['user_id'] ?? null,
            'expires_at' => $expiresAt->toIso8601String(),
        ], $expiresAt);

        return ['token' => $token, 'expires_at' => $expiresAt];
    }

    /**
     * The ticket, or null when the token is unknown, expired or already used.
     *
     * @return Ticket|null
     */
    public static function find(string $token): ?array
    {
        /** @var Ticket|null $ticket */
        $ticket = Cache::get(self::key($token));

        return is_array($ticket) ? $ticket : null;
    }

    /**
     * A lock so two uploads cannot use the same token at once.
     */
    public static function lock(string $token): Lock
    {
        return Cache::lock(self::key($token).':lock', 60);
    }

    public static function consume(string $token): void
    {
        Cache::forget(self::key($token));
    }

    public static function minutes(): int
    {
        return max(1, (int) config('portfolio.mcp.uploads.minutes', 15));
    }

    public static function maxBytes(): int
    {
        return (int) config('portfolio.mcp.images.max_kilobytes') * 1024;
    }

    private static function key(string $token): string
    {
        return 'mcp-upload:'.hash('sha256', $token);
    }
}
