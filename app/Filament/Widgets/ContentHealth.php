<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\EditProfile;
use App\Filament\Pages\SiteSettings;
use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Resources\Experiences\ExperienceResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\Socials\SocialResource;
use App\Models\Article;
use App\Models\Experience;
use App\Models\Profile;
use App\Models\Project;
use App\Models\SiteSetting;
use App\Models\Social;
use Filament\Widgets\Widget;

/**
 * Checklist of missing content that hurts the site or its SEO, each with a link to fix it.
 */
class ContentHealth extends Widget
{
    protected static ?int $sort = 3;

    protected string $view = 'filament.widgets.content-health';

    protected int|string|array $columnSpan = ['md' => 2, 'xl' => 1];

    /**
     * @return list<array{label: string, ok: bool, hint: string|null, url: string}>
     */
    public function getChecks(): array
    {
        $profile = Profile::current();
        $settings = SiteSetting::current();
        $projectsWithoutCover = Project::query()->whereDoesntHave('media', fn ($query) => $query->where('collection_name', 'cover'))->count();
        $projectsWithoutAlt = Project::query()->whereHas('media', fn ($query) => $query->where('collection_name', 'cover'))->where(fn ($query) => $query->whereNull('cover_alt')->orWhere('cover_alt', ''))->count();
        $articlesWithoutExcerpt = Article::query()->where(fn ($query) => $query->whereNull('excerpt')->orWhere('excerpt', ''))->count();

        return [
            ['label' => 'Portrait uploaded', 'ok' => $profile->hasMedia('portrait'), 'hint' => null, 'url' => EditProfile::getUrl()],
            ['label' => 'Bio written', 'ok' => filled($profile->summary) && $profile->story !== [], 'hint' => 'Summary and story paragraphs', 'url' => EditProfile::getUrl(['tab' => 'bio::data::tab'])],
            ['label' => 'Public email set', 'ok' => filled($profile->email), 'hint' => 'Used by the contact section', 'url' => EditProfile::getUrl()],
            ['label' => 'Work experience added', 'ok' => Experience::query()->visible()->exists(), 'hint' => null, 'url' => ExperienceResource::getUrl()],
            ['label' => 'Social links added', 'ok' => Social::query()->visible()->exists(), 'hint' => null, 'url' => SocialResource::getUrl()],
            ['label' => 'Every project has a cover', 'ok' => $projectsWithoutCover === 0, 'hint' => $projectsWithoutCover > 0 ? "{$projectsWithoutCover} missing" : null, 'url' => ProjectResource::getUrl()],
            ['label' => 'Every cover has alt text', 'ok' => $projectsWithoutAlt === 0, 'hint' => $projectsWithoutAlt > 0 ? "{$projectsWithoutAlt} missing" : null, 'url' => ProjectResource::getUrl()],
            ['label' => 'Every article has an excerpt', 'ok' => $articlesWithoutExcerpt === 0, 'hint' => $articlesWithoutExcerpt > 0 ? "{$articlesWithoutExcerpt} missing" : null, 'url' => ArticleResource::getUrl()],
            ['label' => 'Default meta description', 'ok' => filled($settings->meta_description), 'hint' => null, 'url' => SiteSettings::getUrl()],
            ['label' => 'Default social image', 'ok' => $settings->hasMedia('default_og_image'), 'hint' => '1200×630', 'url' => SiteSettings::getUrl()],
            ['label' => 'Search engines allowed', 'ok' => $settings->indexable, 'hint' => $settings->indexable ? null : 'The site is set to noindex', 'url' => SiteSettings::getUrl()],
        ];
    }
}
