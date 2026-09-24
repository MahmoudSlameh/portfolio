<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Profile;
use App\Models\Project;
use App\Models\SiteSetting;
use App\Support\Seo\Seo;
use Illuminate\Http\Response;

/**
 * Crawling endpoints: sitemap.xml, robots.txt and the RSS feed of articles.
 */
class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $settings = SiteSetting::current();
        $projects = Project::query()->published()->orderByDesc('year')->get(['slug', 'updated_at']);
        $articles = $settings->isPageEnabled('writing')
            ? Article::query()->published()->latest('published_at')->get(['slug', 'updated_at', 'published_at'])
            : collect();

        $urls = collect([
            ['loc' => Seo::url('/'), 'lastmod' => Profile::current()->updated_at, 'priority' => '1.0'],
            ['loc' => Seo::url('/projects'), 'lastmod' => $projects->max('updated_at'), 'priority' => '0.9'],
        ])
            ->merge($projects->map(fn (Project $project): array => ['loc' => Seo::url("/projects/{$project->slug}"), 'lastmod' => $project->updated_at, 'priority' => '0.8']))
            ->when($settings->isPageEnabled('writing'), fn ($urls) => $urls
                ->push(['loc' => Seo::url('/writing'), 'lastmod' => $articles->max('updated_at'), 'priority' => '0.8'])
                ->merge($articles->map(fn (Article $article): array => ['loc' => Seo::url("/writing/{$article->slug}"), 'lastmod' => $article->updated_at, 'priority' => '0.7'])))
            ->merge(collect(['books', 'uses', 'now'])
                ->filter(fn (string $page): bool => $settings->isPageEnabled($page))
                ->map(fn (string $page): array => ['loc' => Seo::url("/{$page}"), 'lastmod' => null, 'priority' => '0.5']));

        return response()
            ->view('feeds.sitemap', ['urls' => $settings->indexable ? $urls : collect()])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $lines = SiteSetting::current()->indexable
            ? ['User-agent: *', 'Allow: /', 'Disallow: /admin', 'Disallow: /livewire', '', 'Sitemap: '.Seo::url('/sitemap.xml')]
            : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines)."\n")->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function rss(): Response
    {
        abort_unless(SiteSetting::current()->isPageEnabled('writing'), 404);

        return response()
            ->view('feeds.rss', [
                'settings' => SiteSetting::current(),
                'profile' => Profile::current(),
                'articles' => Article::query()->published()->latest('published_at')->limit(30)->get(),
            ])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
