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
use App\Models\Testimonial;
use App\Models\User;
use App\Models\UsesGroup;
use App\Models\UsesItem;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureMorphMap();
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
