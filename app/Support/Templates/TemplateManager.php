<?php

namespace App\Support\Templates;

use App\Models\SiteSetting;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\Request;

/**
 * Decides which template renders the public site.
 *
 * Visitors always get the template activated in the panel. The signed-in owner can preview another
 * one with `?template=<id>` (kept in the session until `?template=reset`); see docs/06 § Preview.
 */
final class TemplateManager
{
    private const SESSION_KEY = 'template.preview';

    /** Stateless override used by the developer gallery (/dev/templates) when it is enabled. */
    public const GALLERY_QUERY = '_template';

    /**
     * Resolved template per request object (the manager may outlive a single request).
     *
     * @var array<int, TemplateDefinition>
     */
    private array $resolved = [];

    public function __construct(private readonly TemplateRegistry $registry) {}

    /**
     * The template activated in the panel, or the default one when it no longer exists.
     */
    public function active(): TemplateDefinition
    {
        return $this->registry->find(SiteSetting::current()->active_template) ?? $this->registry->default();
    }

    public function current(): TemplateDefinition
    {
        $request = $this->request();

        return $this->resolved[spl_object_id($request)] ??= $this->resolve($request);
    }

    /**
     * Whether this request renders a template chosen by the developer gallery (`?_template=`).
     */
    public function isGalleryRender(): bool
    {
        return config('portfolio.templates.dev_gallery') === true
            && $this->registry->has((string) $this->request()->query(self::GALLERY_QUERY));
    }

    /**
     * Light/dark theme of the request: `?_theme=` in a gallery render, otherwise the visitor's cookie.
     *
     * @return 'light'|'dark'|null
     */
    public function theme(): ?string
    {
        $request = $this->request();
        $theme = $this->isGalleryRender() ? $request->query('_theme') : $request->cookie('theme');

        return in_array($theme, ['light', 'dark'], true) ? $theme : null;
    }

    public function isPreview(): bool
    {
        return $this->current()->id !== $this->active()->id;
    }

    /**
     * Inertia component name for a page of the current template, e.g. "terminal/Home".
     */
    public function page(string $name): string
    {
        return "{$this->current()->id}/{$name}";
    }

    public function canPreview(): bool
    {
        $user = $this->request()->user();

        return $user instanceof User && $user->canAccessPanel(Filament::getPanel('admin'));
    }

    private function request(): Request
    {
        return request();
    }

    private function resolve(Request $request): TemplateDefinition
    {
        // Gallery iframes each render their own template without touching the (shared) session.
        if ($this->isGalleryRender()) {
            return $this->registry->find((string) $request->query(self::GALLERY_QUERY)) ?? $this->active();
        }

        $session = $request->hasSession() ? $request->session() : null;

        if ($request->query->has('template') && $session !== null) {
            $requested = $this->registry->find((string) $request->query('template'));

            $requested !== null && $this->canPreview()
                ? $session->put(self::SESSION_KEY, $requested->id)
                : $session->forget(self::SESSION_KEY);
        }

        $stored = $session?->get(self::SESSION_KEY);
        $preview = $this->registry->find(is_string($stored) ? $stored : null);

        return $preview !== null && $this->canPreview() ? $preview : $this->active();
    }
}
