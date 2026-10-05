<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the MCP endpoint after Passport has authenticated the token: the connector can be switched
 * off (MCP_ENABLED=false), and only the site owner may use it, even with a valid token.
 */
class EnsureMcpAccess
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('portfolio.mcp.enabled')) {
            abort(404);
        }

        $user = $request->user();

        if (! $user instanceof User || ! $user->isOwner()) {
            return response()->json(['message' => 'This account may not use the portfolio connector.'], 403);
        }

        return $next($request);
    }
}
