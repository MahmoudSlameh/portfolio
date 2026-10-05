<?php

use App\Mcp\Servers\PortfolioServer;
use App\Mcp\Tools\Projects\CreateProjectTool;
use App\Mcp\Tools\Projects\DeleteProjectTool;
use App\Mcp\Tools\Projects\GetProjectTool;
use App\Mcp\Tools\Projects\ListProjectsTool;
use App\Mcp\Tools\Projects\RestoreProjectTool;
use App\Mcp\Tools\Projects\UpdateProjectTool;
use App\Models\Company;
use App\Models\Project;
use App\Models\Skill;

test('create_project saves a draft with its story, architecture and stack', function () {
    Skill::factory()->create(['name' => 'Laravel']);
    $company = Company::factory()->create(['name' => 'Acme']);

    PortfolioServer::tool(CreateProjectTool::class, [
        'title' => 'Ledger',
        'tagline' => 'A double-entry ledger that never drifts.',
        'summary' => 'Books every payment twice.',
        'year' => 2024,
        'version' => 'v3.2.0',
        'status' => 'maintained',
        'category' => 'open-source',
        'company_id' => $company->id,
        'stack' => ['laravel', 'PostgreSQL', 'React'],
        'overview' => ['First paragraph.', 'Second paragraph.'],
        'features' => [['title' => 'Audit trail', 'description' => 'Every change is kept.']],
        'metrics' => [['value' => '12 min', 'label' => 'close time']],
        'links' => [['label' => 'Source', 'url' => 'https://github.com/acme/ledger', 'kind' => 'source']],
        'architecture' => [
            'columns' => 2,
            'rows' => 1,
            'nodes' => [
                ['id' => 'api', 'label' => 'API', 'kind' => 'service', 'column' => 1, 'row' => 1],
                ['id' => 'db', 'label' => 'Database', 'kind' => 'store', 'column' => 2, 'row' => 1],
            ],
            'edges' => [['from' => 'api', 'to' => 'db']],
        ],
    ])
        ->assertOk()
        ->assertHasNoErrors()
        ->assertSee('Created project \"Ledger\" as a draft.');

    $project = Project::query()->sole();

    expect($project->slug)->toBe('ledger')
        ->and($project->is_published)->toBeFalse()
        ->and($project->company_id)->toBe($company->id)
        ->and($project->stack)->toBe(['Laravel', 'PostgreSQL', 'React'])
        ->and($project->overview)->toBe(['First paragraph.', 'Second paragraph.'])
        ->and($project->metrics)->toBe([['value' => '12 min', 'label' => 'close time', 'detail' => '']])
        ->and($project->architecture['caption'])->toBe('')
        ->and($project->architecture['nodes'][0])->toBe(['id' => 'api', 'label' => 'API', 'detail' => '', 'kind' => 'service', 'column' => 1, 'row' => 1])
        ->and($project->architecture['edges'])->toBe([['from' => 'api', 'to' => 'db']]);

    // Existing skills are reused (case-insensitively); the others are created without a category.
    expect(Skill::query()->count())->toBe(3)
        ->and(Skill::query()->where('name', 'React')->value('skill_category_id'))->toBeNull();
});

test('create_project defaults the year and validates its input', function () {
    PortfolioServer::tool(CreateProjectTool::class, ['title' => 'Atlas'])->assertOk();

    expect(Project::query()->sole()->year)->toBe((int) date('Y'));

    PortfolioServer::tool(CreateProjectTool::class, ['tagline' => 'No title'])
        ->assertHasErrors(['The title field is required.']);

    PortfolioServer::tool(CreateProjectTool::class, ['title' => 'Bad', 'status' => 'shipped', 'company_id' => 999])
        ->assertHasErrors();

    PortfolioServer::tool(CreateProjectTool::class, [
        'title' => 'Graph',
        'architecture' => [
            'columns' => 1,
            'rows' => 1,
            'nodes' => [['id' => 'api', 'label' => 'API', 'kind' => 'service', 'column' => 2, 'row' => 1]],
            'edges' => [['from' => 'api', 'to' => 'missing']],
        ],
    ])->assertHasErrors([
        'Architecture node "api" is outside the grid (1 columns × 1 rows).',
        'Architecture edge to "missing" is not a node id.',
    ]);

    expect(Project::query()->count())->toBe(1);
});

test('update_project changes only the given fields', function () {
    $project = Project::factory()->create(['title' => 'Atlas', 'tagline' => 'Old', 'summary' => 'Keep me']);
    $project->syncSkillsInOrder([Skill::factory()->create(['name' => 'Vue'])->id]);

    PortfolioServer::tool(UpdateProjectTool::class, [
        'project' => 'atlas',
        'tagline' => 'New tagline',
        'is_published' => true,
        'stack' => ['Svelte'],
    ])->assertOk()->assertSee('Updated project \"Atlas\": tagline, is_published, stack.');

    $project->refresh();

    expect($project->tagline)->toBe('New tagline')
        ->and($project->summary)->toBe('Keep me')
        ->and($project->is_published)->toBeTrue()
        ->and($project->stack)->toBe(['Svelte']);

    PortfolioServer::tool(UpdateProjectTool::class, ['project' => (string) $project->id, 'tagline' => null])->assertOk();
    expect($project->refresh()->tagline)->toBeNull();

    PortfolioServer::tool(UpdateProjectTool::class, ['project' => $project->id])
        ->assertHasErrors(['Nothing to update: send at least one field to change.']);
    PortfolioServer::tool(UpdateProjectTool::class, ['project' => 'nope', 'title' => 'X'])
        ->assertHasErrors(['No project matches "nope". Call list_projects to find its id or slug.']);
});

test('update_project keeps slugs unique', function () {
    Project::factory()->create(['slug' => 'taken']);
    $project = Project::factory()->create(['slug' => 'mine']);

    PortfolioServer::tool(UpdateProjectTool::class, ['project' => 'mine', 'slug' => 'taken'])->assertHasErrors();
    PortfolioServer::tool(UpdateProjectTool::class, ['project' => 'mine', 'slug' => 'mine'])->assertOk();

    expect($project->refresh()->slug)->toBe('mine');
});

test('list_projects filters and get_project returns every field', function () {
    Project::factory()->create(['title' => 'Atlas', 'is_published' => true]);
    Project::factory()->create(['title' => 'Beacon', 'is_published' => false]);
    Project::factory()->create(['title' => 'Comet'])->delete();

    PortfolioServer::tool(ListProjectsTool::class)
        ->assertOk()
        ->assertStructuredContent(fn ($json) => $json->where('total', 2)->etc());

    PortfolioServer::tool(ListProjectsTool::class, ['published' => false])
        ->assertSee('Beacon')
        ->assertDontSee('Atlas');

    PortfolioServer::tool(ListProjectsTool::class, ['include_deleted' => true, 'search' => 'com'])
        ->assertSee('Comet')
        ->assertSee('"is_deleted":true');

    PortfolioServer::tool(GetProjectTool::class, ['project' => 'atlas'])
        ->assertOk()
        ->assertSee(['"title":"Atlas"', '"gallery":[]', '/admin/projects/']);
});

test('delete_project moves a project to the trash and restore_project brings it back', function () {
    $project = Project::factory()->create(['title' => 'Atlas']);

    PortfolioServer::tool(DeleteProjectTool::class, ['project' => 'atlas'])->assertOk()->assertSee('trash');
    expect($project->refresh()->trashed())->toBeTrue();

    PortfolioServer::tool(DeleteProjectTool::class, ['project' => 'atlas'])->assertHasErrors();

    PortfolioServer::tool(RestoreProjectTool::class, ['project' => 'atlas'])->assertOk();
    expect($project->refresh()->trashed())->toBeFalse();
});

test('the project tools describe themselves for Claude', function () {
    PortfolioServer::tool(DeleteProjectTool::class, ['project' => 'x'])
        ->assertName('delete_project')
        ->assertTitle('Delete a project');
});
