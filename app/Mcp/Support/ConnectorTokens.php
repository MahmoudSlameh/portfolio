<?php

namespace App\Mcp\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;
use Laravel\Mcp\Server\Registrar;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\PersonalAccessTokenResult;
use Laravel\Passport\Token;
use RuntimeException;

/**
 * The tokens that give Claude access to the connector: personal access tokens made in the panel (for
 * Claude Code and other clients that take a bearer token) and the OAuth tokens of connected apps.
 */
final class ConnectorTokens
{
    public const PERSONAL_CLIENT_NAME = 'Portfolio personal access tokens';

    /**
     * A personal access token with the MCP scope. The plain token is only available here, once.
     */
    public static function create(User $user, string $name): PersonalAccessTokenResult
    {
        $clients = app(ClientRepository::class);
        $provider = (string) config('auth.guards.api.provider');

        try {
            $clients->personalAccessClient($provider);
        } catch (RuntimeException) {
            $clients->createPersonalAccessGrantClient(self::PERSONAL_CLIENT_NAME, $provider);
        }

        return $user->createToken($name, [Registrar::OAUTH_SCOPE]);
    }

    /**
     * The user's tokens that still work, newest first.
     *
     * @return Builder<Token>
     */
    public static function active(User $user): Builder
    {
        return Token::query()
            ->with('client')
            ->where('user_id', $user->getKey())
            ->where('revoked', false)
            ->where('expires_at', '>', Date::now())
            ->latest();
    }

    public static function isPersonal(Token $token): bool
    {
        return $token->client?->hasGrantType('personal_access') ?? false;
    }

    /**
     * Revokes the token and its refresh token, so the app cannot get a new one.
     */
    public static function revoke(Token $token): void
    {
        $token->revoke();
        $token->refreshToken?->revoke();
    }
}
