<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Support\Cv\CvOptions;
use App\Support\Cv\CvRenderer;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * The CV as HTML for the panel's live preview (P11-04). Only for users who can open the panel; everyone
 * else gets a 404, like any unknown URL.
 */
class CvPreviewController
{
    public function __invoke(Request $request, CvRenderer $renderer): Response
    {
        $user = $request->user();

        abort_unless($user instanceof User && $user->canAccessPanel(Filament::getPanel('admin')), 404);

        return response($renderer->html(CvOptions::fromArray($request->query()), preview: true))
            ->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Cache-Control', 'no-store');
    }
}
