<?php

namespace App\Mcp\Tools\Profile;

use App\Enums\ProjectCategory;
use App\Enums\ProjectStatus;
use App\Models\Article;
use App\Models\Company;
use App\Models\Experience;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use App\Models\SkillCategory;
use App\Support\Media\PageScreenshot;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Name('get_portfolio_overview')]
#[Title('Portfolio overview')]
#[Description('Start here: who the portfolio belongs to, its URLs, how much content it has, the latest projects and what this server can do (e.g. whether screenshots are available).')]
#[IsReadOnly]
class GetPortfolioOverviewTool extends Tool
{
    public function handle(Request $request): ResponseFactory
    {
        $profile = Profile::current();

        return Response::structured([
            'owner' => ['name' => $profile->name, 'role' => $profile->role, 'headline' => $profile->headline],
            'urls' => ['site' => url('/'), 'admin' => url('/admin')],
            'counts' => [
                'projects' => Project::query()->count(),
                'published_projects' => Project::query()->published()->count(),
                'draft_projects' => Project::query()->where('is_published', false)->count(),
                'deleted_projects' => Project::onlyTrashed()->count(),
                'experiences' => Experience::query()->count(),
                'companies' => Company::query()->count(),
                'skills' => Skill::query()->count(),
                'skill_categories' => SkillCategory::query()->count(),
                'articles' => Article::query()->count(),
            ],
            'latest_projects' => Project::query()->latest('updated_at')->limit(10)->get(['id', 'slug', 'title', 'year', 'is_published'])
                ->map(fn (Project $project): array => $project->only(['id', 'slug', 'title', 'year', 'is_published']))->all(),
            'project_statuses' => array_column(ProjectStatus::cases(), 'value'),
            'project_categories' => array_column(ProjectCategory::cases(), 'value'),
            'capabilities' => [
                'screenshots' => PageScreenshot::available(),
                'direct_uploads' => true, // request_image_upload
                'max_image_mb' => (int) config('portfolio.mcp.images.max_kilobytes') / 1024,
            ],
        ]);
    }
}
