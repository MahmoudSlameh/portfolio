<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 404s a secondary page (writing, books, uses, now) that was switched off in Site → Settings.
 */
class EnsurePageEnabled
{
    public function handle(Request $request, Closure $next, string $page): Response
    {
        abort_unless(SiteSetting::current()->isPageEnabled($page), 404);

        return $next($request);
    }
}
