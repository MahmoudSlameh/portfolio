<?php

namespace App\Mcp\Servers;

use App\Mcp\Prompts\AddProjectFromGithubPrompt;
use App\Mcp\Resources\ContentGuideResource;
use App\Mcp\Tools\Articles\AddArticleImageTool;
use App\Mcp\Tools\Articles\CreateArticleTool;
use App\Mcp\Tools\Articles\DeleteArticleTool;
use App\Mcp\Tools\Articles\GetArticleTool;
use App\Mcp\Tools\Articles\ListArticlesTool;
use App\Mcp\Tools\Articles\RestoreArticleTool;
use App\Mcp\Tools\Articles\SetArticleCoverTool;
use App\Mcp\Tools\Articles\UpdateArticleTool;
use App\Mcp\Tools\Companies\CreateCompanyTool;
use App\Mcp\Tools\Companies\DeleteCompanyTool;
use App\Mcp\Tools\Companies\ListCompaniesTool;
use App\Mcp\Tools\Companies\SetCompanyLogoTool;
use App\Mcp\Tools\Companies\UpdateCompanyTool;
use App\Mcp\Tools\Experiences\CreateExperienceTool;
use App\Mcp\Tools\Experiences\DeleteExperienceTool;
use App\Mcp\Tools\Experiences\ListExperiencesTool;
use App\Mcp\Tools\Experiences\UpdateExperienceTool;
use App\Mcp\Tools\Profile\GetPortfolioOverviewTool;
use App\Mcp\Tools\Profile\GetProfileTool;
use App\Mcp\Tools\Profile\UpdateProfileTool;
use App\Mcp\Tools\Projects\AddProjectImageTool;
use App\Mcp\Tools\Projects\CreateProjectTool;
use App\Mcp\Tools\Projects\DeleteProjectTool;
use App\Mcp\Tools\Projects\GetProjectTool;
use App\Mcp\Tools\Projects\ListProjectsTool;
use App\Mcp\Tools\Projects\RemoveProjectImageTool;
use App\Mcp\Tools\Projects\RestoreProjectTool;
use App\Mcp\Tools\Projects\SetProjectCoverTool;
use App\Mcp\Tools\Projects\UpdateProjectImageTool;
use App\Mcp\Tools\Projects\UpdateProjectTool;
use App\Mcp\Tools\Skills\DeleteSkillCategoryTool;
use App\Mcp\Tools\Skills\DeleteSkillTool;
use App\Mcp\Tools\Skills\ListSkillsTool;
use App\Mcp\Tools\Skills\SaveSkillCategoryTool;
use App\Mcp\Tools\Skills\SaveSkillTool;
use App\Mcp\Tools\Uploads\RequestImageUploadTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

/**
 * The Claude connector (docs/13-mcp-connector.md): reads and edits the portfolio's content, the same
 * content the Filament panel manages.
 */
#[Name('Portfolio')]
#[Version('1.0.0')]
#[Instructions(<<<'TEXT'
This server manages the content of the owner's developer portfolio website: projects (case studies), blog articles, work experience, companies & clients, skills and the profile. Everything you save is what visitors read, so be accurate.

- Start with get_portfolio_overview. Before creating anything, list what exists (list_projects, list_articles, list_skills, list_companies, list_experiences) and update instead of duplicating.
- To add a project from a GitHub repository, follow the add_project_from_github prompt: research the repository first, then create_project, then images (set_project_cover, add_project_image). The portfolio://guides/content resource explains where each field appears and how long it should be.
- Use only facts from the sources or the owner. Never invent metrics, clients, dates or team sizes; leave fields out instead.
- Projects and articles are created as drafts. Publish (is_published) or feature (is_featured) only when the owner asks. Delete only on request; deleted projects and articles can be restored with restore_project / restore_article.
- Articles: the body is Markdown (see create_article and the content guide). To edit one, get_article, change its Markdown and send it back with update_article. Images in the body: write placeholders like ![Step 1](image:step-1), then fill them with add_article_image or request_image_upload (target article_image, placeholder step-1). Cite sources in "sources"; a YouTube link goes in video_url.
- Updates are partial, but list fields replace the stored list: read the record first when adding to a list.
- Images: for a local file (screenshot, mockup) call request_image_upload, then upload the file with the returned curl command (PUT to this site); it is attached as cover, gallery image or company logo. For an image already online use image_url (e.g. raw.githubusercontent.com); screenshot_url makes the server screenshot a public page (check capabilities.screenshots in the overview); image_base64 only for tiny images (< 200 KB). Always write meaningful alt text.
- Write in the owner's voice. Projects, experience and the profile are in English. An article is written in its own language: set language "ar" for Arabic (shown right to left) and still give it an English slug. Finish by giving the owner the admin link from the tool result so they can review.
TEXT)]
class PortfolioServer extends Server
{
    /**
     * List every tool on one page: some clients only read the first page.
     */
    public int $defaultPaginationLength = 50;

    protected array $tools = [
        GetPortfolioOverviewTool::class,
        GetProfileTool::class,
        UpdateProfileTool::class,

        ListProjectsTool::class,
        GetProjectTool::class,
        CreateProjectTool::class,
        UpdateProjectTool::class,
        DeleteProjectTool::class,
        RestoreProjectTool::class,
        SetProjectCoverTool::class,
        AddProjectImageTool::class,
        UpdateProjectImageTool::class,
        RemoveProjectImageTool::class,

        ListArticlesTool::class,
        GetArticleTool::class,
        CreateArticleTool::class,
        UpdateArticleTool::class,
        DeleteArticleTool::class,
        RestoreArticleTool::class,
        SetArticleCoverTool::class,
        AddArticleImageTool::class,

        ListSkillsTool::class,
        SaveSkillTool::class,
        DeleteSkillTool::class,
        SaveSkillCategoryTool::class,
        DeleteSkillCategoryTool::class,

        ListCompaniesTool::class,
        CreateCompanyTool::class,
        UpdateCompanyTool::class,
        DeleteCompanyTool::class,
        SetCompanyLogoTool::class,

        RequestImageUploadTool::class,

        ListExperiencesTool::class,
        CreateExperienceTool::class,
        UpdateExperienceTool::class,
        DeleteExperienceTool::class,
    ];

    protected array $resources = [
        ContentGuideResource::class,
    ];

    protected array $prompts = [
        AddProjectFromGithubPrompt::class,
    ];
}
