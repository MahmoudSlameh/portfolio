<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\ProjectArchiveRequest;
use App\Models\Project;
use App\Support\Content\PortfolioContent;
use App\Support\Seo\JsonLd;
use App\Support\Seo\Seo;
use App\Support\Templates\TemplateManager;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(ProjectArchiveRequest $request, PortfolioContent $content, TemplateManager $templates, Seo $seo): Response
    {
        $filters = $request->filters();
        $projects = $content->projects([
            'search' => $filters['q'] ?? null,
            'tech' => $filters['tech'] ?? null,
            'category' => $filters['category'] ?? null,
            'sort' => match ($filters['sort'] ?? null) {
                'oldest' => 'oldest',
                'newest' => 'newest',
                default => null,
            },
        ]);
        $total = Project::query()->published()->count();

        return Inertia::render($templates->page('ProjectArchive'), [
            'projects' => $projects,
            'facets' => $content->projectFacets(),
            'total' => $total,
            'search' => (object) $filters,
            'seo' => $seo->page(
                title: 'Projects',
                description: "Case studies and projects — {$total} write-ups covering the problem, the approach, the architecture and the results.",
                path: '/projects',
                jsonLd: [
                    JsonLd::page('CollectionPage', 'Projects', '/projects', array_map(
                        fn (array $project): array => ['name' => $project['title'], 'path' => "/projects/{$project['slug']}"],
                        $projects,
                    )),
                    JsonLd::breadcrumbs(['Projects' => '/projects']),
                ],
            ),
        ]);
    }

    public function show(string $slug, PortfolioContent $content, TemplateManager $templates, Seo $seo): Response
    {
        $project = Project::query()->published()->where('slug', $slug)->with(['company', 'skills', 'media'])->firstOrFail();
        $detail = $content->projectBySlug($slug);
        abort_if($detail === null, 404);

        return Inertia::render($templates->page('CaseStudy'), [
            'project' => $detail,
            'seo' => $seo->page(
                title: $project->meta_title ?: "{$project->title} — case study",
                description: $project->meta_description ?: ($project->summary ?: $project->tagline),
                path: "/projects/{$project->slug}",
                type: 'article',
                image: $project->getFirstMedia('cover'),
                imageAlt: $project->cover_alt ?? $project->title,
                publishedAt: $project->published_at,
                modifiedAt: $project->updated_at,
                tags: $project->stack,
                jsonLd: [
                    JsonLd::project($project),
                    JsonLd::breadcrumbs(['Projects' => '/projects', $project->title => "/projects/{$project->slug}"]),
                ],
            ),
        ]);
    }
}
