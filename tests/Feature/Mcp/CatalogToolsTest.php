<?php

use App\Mcp\Servers\PortfolioServer;
use App\Mcp\Tools\Companies\CreateCompanyTool;
use App\Mcp\Tools\Companies\DeleteCompanyTool;
use App\Mcp\Tools\Companies\ListCompaniesTool;
use App\Mcp\Tools\Companies\UpdateCompanyTool;
use App\Mcp\Tools\Experiences\CreateExperienceTool;
use App\Mcp\Tools\Experiences\DeleteExperienceTool;
use App\Mcp\Tools\Experiences\ListExperiencesTool;
use App\Mcp\Tools\Experiences\UpdateExperienceTool;
use App\Mcp\Tools\Profile\GetPortfolioOverviewTool;
use App\Mcp\Tools\Profile\GetProfileTool;
use App\Mcp\Tools\Profile\UpdateProfileTool;
use App\Mcp\Tools\Skills\DeleteSkillCategoryTool;
use App\Mcp\Tools\Skills\DeleteSkillTool;
use App\Mcp\Tools\Skills\ListSkillsTool;
use App\Mcp\Tools\Skills\SaveSkillCategoryTool;
use App\Mcp\Tools\Skills\SaveSkillTool;
use App\Models\Company;
use App\Models\Experience;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use App\Models\SkillCategory;

test('save_skill creates, matches by name and updates skills', function () {
    PortfolioServer::tool(SaveSkillTool::class, ['name' => 'Laravel', 'category' => 'Frameworks', 'icon' => 'laravel', 'proficiency' => 5])
        ->assertOk()
        ->assertSee('Created skill \"Laravel\".');

    $skill = Skill::query()->sole();
    expect($skill->category?->name)->toBe('Frameworks')->and($skill->proficiency)->toBe(5);

    PortfolioServer::tool(SaveSkillTool::class, ['name' => 'laravel', 'years' => 8])->assertSee('Updated skill \"Laravel\".');
    expect($skill->refresh()->years)->toBe(8)->and(Skill::query()->count())->toBe(1);

    PortfolioServer::tool(SaveSkillTool::class, ['id' => $skill->id, 'category' => null])->assertOk();
    expect($skill->refresh()->skill_category_id)->toBeNull();

    Skill::factory()->create(['name' => 'Vue']);
    PortfolioServer::tool(SaveSkillTool::class, ['id' => $skill->id, 'name' => 'vue'])
        ->assertHasErrors(['Another skill is already called "vue".']);
    PortfolioServer::tool(SaveSkillTool::class, ['name' => 'Go', 'icon' => 'Not A Slug'])->assertHasErrors();
    PortfolioServer::tool(SaveSkillTool::class, ['id' => 999, 'years' => 1])->assertHasErrors(['No skill has id 999. Call list_skills to find it.']);
});

test('skill categories are saved, listed and deleted without losing their skills', function () {
    PortfolioServer::tool(SaveSkillCategoryTool::class, ['name' => 'Languages', 'description' => 'What I write'])->assertOk();
    $category = SkillCategory::query()->sole();
    Skill::factory()->create(['name' => 'PHP', 'skill_category_id' => $category->id]);

    PortfolioServer::tool(ListSkillsTool::class)
        ->assertOk()
        ->assertSee(['"name":"Languages"', '"name":"PHP"', '"category":"Languages"']);

    PortfolioServer::tool(SaveSkillCategoryTool::class, ['name' => 'languages', 'sort_order' => 3])->assertSee('Updated');
    expect($category->refresh()->sort_order)->toBe(3);

    PortfolioServer::tool(DeleteSkillCategoryTool::class, ['id' => $category->id])->assertOk();
    expect(SkillCategory::query()->count())->toBe(0)
        ->and(Skill::query()->sole()->skill_category_id)->toBeNull();
});

test('delete_skill removes it from stacks', function () {
    $skill = Skill::factory()->create(['name' => 'jQuery']);
    $project = Project::factory()->create();
    $project->syncSkillsInOrder([$skill->id]);

    PortfolioServer::tool(DeleteSkillTool::class, ['id' => $skill->id])
        ->assertSee('Deleted skill \"jQuery\" (it was in 1 project and 0 experience stacks).');

    expect($project->refresh()->stack)->toBe([]);
});

test('companies are created, updated, listed and deleted', function () {
    PortfolioServer::tool(CreateCompanyTool::class, [
        'name' => 'Acme Corp',
        'kind' => 'client',
        'website_url' => 'https://acme.example.com',
        'country_code' => 'de',
    ])->assertOk();

    $company = Company::query()->sole();
    expect($company->slug)->toBe('acme-corp')->and($company->country_code)->toBe('DE');

    PortfolioServer::tool(CreateCompanyTool::class, ['name' => 'Bad', 'country_code' => 'XX'])
        ->assertHasErrors(['country_code must be an ISO 3166 alpha-2 code, e.g. "DE".']);

    PortfolioServer::tool(UpdateCompanyTool::class, ['company' => 'acme-corp', 'industry' => 'Fintech'])->assertOk();
    expect($company->refresh()->industry)->toBe('Fintech');

    PortfolioServer::tool(ListCompaniesTool::class, ['kind' => 'client'])->assertSee('Acme Corp');
    PortfolioServer::tool(ListCompaniesTool::class, ['kind' => 'employer'])->assertDontSee('Acme Corp');

    $project = Project::factory()->create(['company_id' => $company->id]);
    PortfolioServer::tool(DeleteCompanyTool::class, ['company' => $company->id])->assertOk();

    expect(Company::query()->count())->toBe(0)
        ->and($project->refresh()->company_id)->toBeNull();
});

test('experiences are created with month dates and a stack, updated and deleted', function () {
    $company = Company::factory()->create(['name' => 'Acme']);

    PortfolioServer::tool(CreateExperienceTool::class, [
        'company_id' => $company->id,
        'role' => 'Staff Engineer',
        'employment_type' => 'full-time',
        'work_mode' => 'remote',
        'start_date' => '2022-02',
        'highlights' => ['Cut deploy time by half.'],
        'stack' => ['Go', 'Kafka'],
    ])->assertOk()->assertSee('Added \"Staff Engineer\" at Acme.');

    $experience = Experience::query()->sole();

    expect($experience->start_date->toDateString())->toBe('2022-02-01')
        ->and($experience->end_date)->toBeNull()
        ->and($experience->stack)->toBe(['Go', 'Kafka']);

    PortfolioServer::tool(UpdateExperienceTool::class, ['id' => $experience->id, 'end_date' => '2021-01'])
        ->assertHasErrors(['end_date must be on or after start_date.']);
    PortfolioServer::tool(UpdateExperienceTool::class, ['id' => $experience->id, 'end_date' => '2024-06', 'company_id' => null])
        ->assertHasErrors(['Give a company_id or an organization_name.']);
    PortfolioServer::tool(UpdateExperienceTool::class, ['id' => $experience->id, 'end_date' => '2024-06'])->assertOk();
    expect($experience->refresh()->end_date?->toDateString())->toBe('2024-06-01');

    PortfolioServer::tool(CreateExperienceTool::class, ['role' => 'Maintainer', 'start_date' => '2020-01'])
        ->assertHasErrors(['Give a company_id or an organization_name.']);
    PortfolioServer::tool(CreateExperienceTool::class, ['role' => 'Maintainer', 'organization_name' => 'Open source', 'start_date' => '2099-01'])
        ->assertHasErrors();

    PortfolioServer::tool(ListExperiencesTool::class)->assertSee(['"role":"Staff Engineer"', '"start_date":"2022-02"']);

    PortfolioServer::tool(DeleteExperienceTool::class, ['id' => $experience->id])->assertOk();
    expect(Experience::query()->count())->toBe(0);
});

test('the profile can be read and partially updated', function () {
    Profile::current()->update(['name' => 'Ada Lovelace', 'summary' => 'Keep me']);

    PortfolioServer::tool(GetProfileTool::class)->assertSee('"name":"Ada Lovelace"');

    PortfolioServer::tool(UpdateProfileTool::class, [
        'headline' => 'I build calm software.',
        'focus_areas' => ['APIs', 'Tooling'],
        'stats' => [['value' => '10+', 'label' => 'years']],
    ])->assertOk();

    $profile = Profile::current();
    expect($profile->headline)->toBe('I build calm software.')
        ->and($profile->summary)->toBe('Keep me')
        ->and($profile->focus_areas)->toBe(['APIs', 'Tooling']);

    PortfolioServer::tool(UpdateProfileTool::class, ['timezone' => 'Mars/Olympus'])->assertHasErrors();
    PortfolioServer::tool(UpdateProfileTool::class, [])->assertHasErrors(['Nothing to update: send at least one field to change.']);
});

test('the overview counts the content and reports capabilities', function () {
    config()->set('studio.screenshots.chrome', '/usr/bin/chromium');
    Project::factory()->count(2)->create(['is_published' => true]);
    Project::factory()->create(['is_published' => false]);

    PortfolioServer::tool(GetPortfolioOverviewTool::class)
        ->assertOk()
        ->assertStructuredContent(fn ($json) => $json
            ->where('counts.projects', 3)
            ->where('counts.draft_projects', 1)
            ->where('capabilities.screenshots', true)
            ->has('latest_projects', 3)
            ->etc());
});
