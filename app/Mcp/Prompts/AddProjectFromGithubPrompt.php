<?php

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('add_project_from_github')]
#[Title('Add a project from a GitHub repository')]
#[Description('Step-by-step instructions for turning a GitHub repository into a complete portfolio case study: research, draft, images, review.')]
class AddProjectFromGithubPrompt extends Prompt
{
    /**
     * @return list<Argument>
     */
    public function arguments(): array
    {
        return [
            new Argument(name: 'repository', description: 'GitHub URL of the repository, e.g. https://github.com/acme/ledger', required: true),
            new Argument(name: 'notes', description: 'Anything the owner wants emphasised (their role, the client, results), or "publish" to publish it right away.', required: false),
        ];
    }

    public function handle(Request $request): Response
    {
        $data = $request->validate([
            'repository' => ['required', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $notes = filled($data['notes'] ?? null) ? "\n\nNotes from the owner: {$data['notes']}" : '';

        return Response::text(<<<TEXT
Add the GitHub repository {$data['repository']} to my portfolio as a project case study.{$notes}

1. Research the repository with the tools you have (GitHub connector, web fetch or a local clone): README, docs, the main source folders, package manifests (composer.json, package.json, go.mod…), git tags/releases, the commit history (first and last commit dates, size of the team), and the live site if there is one. Do not invent facts or numbers; leave a field out when the sources do not support it.
2. Call get_portfolio_overview, then list_projects (search the name) to make sure the project is not already there; if it is, update it instead of creating a duplicate. Call list_skills and reuse the existing skill names in the stack; call list_companies and list_experiences if the project belongs to a client or a role.
3. Call create_project with: title, tagline, summary, year, version (latest tag), status, category (open-source for public libraries), role, team, timeline, stack (ordered by importance), overview and problem paragraphs, approach, features and challenges (2–5 items each), an architecture diagram when the structure is clear (services, stores, queues, external APIs), metrics only from real figures (stars, downloads, benchmarks in the README), and links (source code, live site, docs). Read the portfolio://guides/content resource for the tone and lengths.
4. Images: set a 16:9 cover and add 2–4 gallery screenshots (4:3 works best). For image files you have locally, call request_image_upload (target "cover" or "gallery") and upload each file with the curl command it returns. For images already online, use set_project_cover / add_project_image with image_url (e.g. a raw.githubusercontent.com URL from the repository) or screenshot_url on the live site when capabilities.screenshots is true. Always write real alt text.
5. Keep the project as a draft unless I asked to publish it. Finish with a short summary of what you filled in, anything you were unsure about, and the admin link from the result so I can review it.
TEXT);
    }
}
