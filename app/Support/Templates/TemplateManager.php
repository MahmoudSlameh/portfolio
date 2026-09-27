<?php

namespace App\Support\Templates;

use App\Models\SiteSetting;
use App\Models\StudioTemplate;
use App\Models\StudioTemplateVersion;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\Request;

/**
 * Decides which template renders the public site.
 *
 * Visitors always get the template activated in the panel. The signed-in owner can preview another
 * one with `?template=<id>` (kept in the session until `?template=reset`); see docs/06 § Preview.
 * For a studio template, `&version=<n>` previews one of its versions instead of the active one.
 */
final class TemplateManager
{
    private const SESSION_KEY = 'template.preview';

    private const SESSION_VERSION_KEY = 'template.preview_version';

    /** Stateless override used by the developer gallery (/dev/templates) when it is enabled. */
    public const GALLERY_QUERY = '_template';

    /**
     * Resolved template per request object (the manager may outlive a single request).
     *
     * @var array<int, TemplateDefinition>
     */
    private array $resolved = [];

    /**
     * Studio template rendered by each request (false: none).
     *
     * @var array<int, StudioTemplate|false>
     */
    private array $studio = [];

    /**
     * Studio version rendered by each request (false: none).
     *
     * @var array<int, StudioTemplateVersion|false>
     */
    private array $versions = [];

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
     * The studio template this request renders (with its active version), if the current template is one.
     */
    public function studio(): ?StudioTemplate
    {
        $key = spl_object_id($this->request());

        if (! array_key_exists($key, $this->studio)) {
            $current = $this->current();
            $this->studio[$key] = $current->isStudio()
                ? (StudioTemplate::query()->renderable()->with('activeVersion')->find(substr($current->id, strlen(StudioTemplate::ID_PREFIX))) ?? false)
                : false;
        }

        return $this->studio[$key] ?: null;
    }

    /**
     * The studio version this request renders: the one being previewed (`&version=`), else the active one.
     */
    public function studioVersion(): ?StudioTemplateVersion
    {
        $key = spl_object_id($this->request());

        if (! array_key_exists($key, $this->versions)) {
            $template = $this->studio();
            $number = $this->previewVersion();
            $version = ($number !== null ? $template?->versions()->where('number', $number)->first() : null)
                ?? $template?->getRelationValue('activeVersion');

            $this->versions[$key] = $version instanceof StudioTemplateVersion ? $version : false;
        }

        return $this->versions[$key] ?: null;
    }

    /**
     * The spec of the rendered studio version.
     *
     * @return array<string, mixed>|null
     */
    public function studioSpec(): ?array
    {
        return $this->studioVersion()?->spec;
    }

    /**
     * The version number the owner is previewing (`?template=studio:<ulid>&version=<n>`), if any.
     */
    public function previewVersion(): ?int
    {
        if ($this->isGalleryRender() || ! $this->current()->isStudio() || ! $this->canPreview()) {
            return null;
        }

        $request = $this->request();
        $number = $request->hasSession() ? $request->session()->get(self::SESSION_VERSION_KEY) : null;

        return is_int($number) && $number > 0 ? $number : null;
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
        return $this->current()->id !== $this->active()->id || $this->previewVersion() !== null;
    }

    /**
     * Inertia component name for a page of the current template, e.g. "terminal/Home".
     */
    public function page(string $name): string
    {
        return "{$this->current()->namespace()}/{$name}";
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

            $version = filter_var($request->query('version'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            $exists = $requested !== null && $requested->isStudio() && $version !== false
                && StudioTemplateVersion::query()
                    ->where('studio_template_id', substr($requested->id, strlen(StudioTemplate::ID_PREFIX)))
                    ->where('number', $version)
                    ->exists();

            $exists && $this->canPreview()
                ? $session->put(self::SESSION_VERSION_KEY, $version)
                : $session->forget(self::SESSION_VERSION_KEY);
        }

        $stored = $session?->get(self::SESSION_KEY);
        $preview = $this->registry->find(is_string($stored) ? $stored : null);

        return $preview !== null && $this->canPreview() ? $preview : $this->active();
    }
}
