<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Book;
use App\Models\Certification;
use App\Models\Company;
use App\Models\ContactMessage;
use App\Models\Education;
use App\Models\Experience;
use App\Models\NowPage;
use App\Models\Profile;
use App\Models\Project;
use App\Models\ProjectGalleryItem;
use App\Models\SiteSetting;
use App\Models\Skill;
use App\Models\SkillCategory;
use App\Models\Social;
use App\Models\StudioTemplate;
use App\Models\StudioTemplateVersion;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\UsesGroup;
use App\Models\UsesItem;
use App\Support\Seo\Seo;
use App\Support\Templates\TemplateDefinition;
use App\Support\Templates\TemplateEjector;
use App\Support\Templates\TemplateManager;
use App\Support\Templates\TemplateRegistry;
use App\Support\Templates\TemplateScaffolder;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Inertia\ExceptionResponse;
use Inertia\Inertia;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TemplateRegistry::class, fn (): TemplateRegistry => new TemplateRegistry(
            path: config('portfolio.templates.path'),
            defaultId: config('portfolio.templates.default'),
            cachePath: $this->app->bootstrapPath('cache/templates.php'),
            studio: fn (): iterable => StudioTemplate::query()
                ->renderable()
                ->with(['activeVersion', 'media'])
                ->latest()
                ->get()
                ->map(fn (StudioTemplate $template): TemplateDefinition => TemplateDefinition::forStudio($template)),
        ));
        $this->app->bind(TemplateScaffolder::class, fn (): TemplateScaffolder => new TemplateScaffolder(
            files: $this->app->make(Filesystem::class),
            registry: $this->app->make(TemplateRegistry::class),
            templatesPath: config('portfolio.templates.path'),
            pagesPath: config('portfolio.templates.pages_path'),
            stylesheet: config('portfolio.templates.stylesheet'),
            screenshotsPath: config('portfolio.templates.screenshots_path'),
        ));
        $this->app->bind(TemplateEjector::class, fn (): TemplateEjector => new TemplateEjector(
            files: $this->app->make(Filesystem::class),
            scaffolder: $this->app->make(TemplateScaffolder::class),
            templatesPath: config('portfolio.templates.path'),
            pagesPath: config('portfolio.templates.pages_path'),
            screenshotsPath: config('portfolio.templates.screenshots_path'),
        ));
        $this->app->scoped(TemplateManager::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureMorphMap();
        $this->configureRateLimiting();
        $this->configureErrorPages();
        $this->configureMediaUploads();
        $this->configurePassport();

        $this->optimizes(optimize: 'template:cache', clear: 'template:clear', key: 'templates');
    }

    /**
     * Panel uploads go to the media-library disk (MEDIA_DISK). Without this, Filament falls back to
     * FILESYSTEM_DISK ("local" = private), so uploaded images got URLs that are never served.
     */
    protected function configureMediaUploads(): void
    {
        SpatieMediaLibraryFileUpload::configureUsing(
            fn (SpatieMediaLibraryFileUpload $upload): SpatieMediaLibraryFileUpload => $upload->disk(config('media-library.disk_name')),
        );
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('contact', fn (Request $request): Limit => Limit::perMinute(5)->by((string) $request->ip()));
        RateLimiter::for('mcp', fn (Request $request): Limit => Limit::perMinute((int) config('portfolio.mcp.rate_limit'))->by('mcp:'.($request->user()?->getAuthIdentifier() ?? $request->ip())));
        RateLimiter::for('mcp-oauth', fn (Request $request): Limit => Limit::perMinute(30)->by('mcp-oauth:'.$request->ip()));
        RateLimiter::for('mcp-upload', fn (Request $request): Limit => Limit::perMinute(30)->by('mcp-upload:'.$request->ip()));
    }

    /**
     * OAuth for the Claude connector (docs/13-mcp-connector.md): short-lived access tokens that Claude
     * refreshes, and a consent screen only the site owner can get past.
     */
    protected function configurePassport(): void
    {
        Passport::tokensExpireIn(CarbonInterval::days(30));
        Passport::refreshTokensExpireIn(CarbonInterval::days(365));
        Passport::personalAccessTokensExpireIn(CarbonInterval::days((int) config('portfolio.mcp.token_days')));

        Passport::authorizationView(function (array $parameters): Response {
            abort_unless($parameters['user'] instanceof User && $parameters['user']->isOwner(), 403);

            return response()->view('mcp.authorize', [...$parameters, 'siteName' => Profile::current()->name]);
        });
    }

    /**
     * Public 404s render the active template's NotFound page (the panel keeps Filament's pages).
     */
    protected function configureErrorPages(): void
    {
        Inertia::handleExceptionsUsing(function (ExceptionResponse $response): ?ExceptionResponse {
            if ($response->statusCode() !== 404 || $response->request->is('admin', 'admin/*', 'livewire*', 'storage/*', 'up') || $response->request->expectsJson()) {
                return null;
            }

            return $response
                ->render(app(TemplateManager::class)->page('NotFound'), [
                    'seo' => app(Seo::class)->page(
                        title: 'Page not found',
                        description: 'This page does not exist.',
                        path: '/'.ltrim($response->request->path(), '/'),
                        noindex: true,
                    ),
                ])
                ->withSharedData();
        });
    }

    /**
     * Store short, stable type names in polymorphic columns (media, skillables, notifications).
     */
    protected function configureMorphMap(): void
    {
        Relation::enforceMorphMap([
            'user' => User::class,
            'profile' => Profile::class,
            'site_setting' => SiteSetting::class,
            'now_page' => NowPage::class,
            'company' => Company::class,
            'experience' => Experience::class,
            'education' => Education::class,
            'certification' => Certification::class,
            'testimonial' => Testimonial::class,
            'skill_category' => SkillCategory::class,
            'skill' => Skill::class,
            'project' => Project::class,
            'project_gallery_item' => ProjectGalleryItem::class,
            'article' => Article::class,
            'book' => Book::class,
            'uses_group' => UsesGroup::class,
            'uses_item' => UsesItem::class,
            'social' => Social::class,
            'contact_message' => ContactMessage::class,
            'studio_template' => StudioTemplate::class,
            'studio_template_version' => StudioTemplateVersion::class,
        ]);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
