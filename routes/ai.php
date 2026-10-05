<?php

use App\Http\Controllers\Mcp\ImageUploadController;
use App\Http\Middleware\EnsureMcpAccess;
use App\Mcp\Servers\PortfolioServer;
use Illuminate\Support\Facades\Route;
use Laravel\Mcp\Facades\Mcp;
use Laravel\Mcp\Server\Registrar;
use Laravel\Passport\Http\Middleware\CheckToken;

/*
| The Claude connector (docs/13-mcp-connector.md).
|
| - OAuth discovery (RFC 9728 / RFC 8414) and dynamic client registration, so claude.ai and Claude
|   Desktop can connect with just the URL. Consent happens on Passport's /oauth/authorize screen.
| - The MCP server itself, for OAuth tokens and personal access tokens (Site → Claude connector).
| - A local stdio server: `php artisan mcp:start portfolio` (no token; whoever runs it has shell access).
*/

Route::middleware('throttle:mcp-oauth')->group(function (): void {
    Mcp::oauthRoutes();
});

Mcp::web('/mcp', PortfolioServer::class)
    ->middleware(['auth:api', CheckToken::using(Registrar::OAUTH_SCOPE), EnsureMcpAccess::class, 'throttle:mcp'])
    ->name('mcp');

// Direct uploads for request_image_upload: the single-use token in the URL is the credential.
Route::match(['put', 'post'], '/mcp/uploads/{token}', ImageUploadController::class)
    ->where('token', '[A-Za-z0-9]{64}')
    ->middleware('throttle:mcp-upload')
    ->name('mcp.uploads');

Mcp::local('portfolio', PortfolioServer::class);
