<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use App\Support\Content\PortfolioContent;
use App\Support\Templates\TemplateManager;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Props shared with every public page (the SharedProps type in resources/js/types/shared.ts).
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $templates = app(TemplateManager::class);
        $content = app(PortfolioContent::class);
        $settings = SiteSetting::current();

        return [
            ...parent::share($request),
            'site' => fn (): array => [
                'name' => $settings->site_name,
                'url' => rtrim((string) config('app.url'), '/'),
                'enabledPages' => collect(SiteSetting::TOGGLEABLE_PAGES)
                    ->mapWithKeys(fn (string $page): array => [$page => $settings->isPageEnabled($page)])
                    ->all(),
            ],
            'profile' => fn (): array => $content->profile(),
            'socials' => fn (): array => $content->socials(),
            'searchIndex' => inertia()->defer(fn (): array => $content->searchIndex())->once(),
            'template' => fn (): array => [
                'id' => $templates->current()->id,
                'name' => $templates->current()->label,
                // The gallery renders templates side by side; the owner's preview bar would only get in the way.
                'isPreview' => $templates->isPreview() && ! $templates->isGalleryRender(),
            ],
            'theme' => $templates->theme(),
            'flash' => fn (): array => ['success' => $request->hasSession() ? $request->session()->get('success') : null],
        ];
    }
}
