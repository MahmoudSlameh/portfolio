<?php

use App\Enums\ProjectCategory;
use App\Enums\ProjectStatus;
use App\Filament\Resources\Companies\Pages\EditCompany;
use App\Filament\Resources\Companies\RelationManagers\ProjectsRelationManager;
use App\Filament\Resources\Projects\Pages\CreateProject;
use App\Filament\Resources\Projects\Pages\EditProject;
use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Models\Company;
use App\Models\Project;
use App\Models\Skill;
use Filament\Forms\Components\Repeater;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(fn () => actingAsAdmin());

test('a full case study can be entered through the form', function () {
    Storage::fake('public');
    $undo = Repeater::fake();
    $company = Company::factory()->create();
    [$go, $postgres] = [Skill::factory()->create(['name' => 'Go']), Skill::factory()->create(['name' => 'PostgreSQL'])];

    Livewire::test(CreateProject::class)
        ->fillForm([
            'title' => 'Ledgerline',
            'tagline' => 'A double-entry ledger that has never been out of balance.',
            'summary' => 'The core money-movement ledger.',
            'year' => 2025,
            'status' => ProjectStatus::Live->value,
            'category' => ProjectCategory::Platform->value,
            'company_id' => $company->id,
            'role' => 'Tech lead',
            'stack' => [(string) $postgres->id, (string) $go->id],
            'overview' => [['paragraph' => 'Tessellate moves money.']],
            'approach' => [['title' => 'Invariants first', 'description' => 'Wrote the rules down.']],
            'architecture' => [
                'caption' => 'Write path',
                'columns' => 2,
                'rows' => 1,
                'nodes' => [
                    ['id' => 'api', 'label' => 'API', 'detail' => 'Go', 'kind' => 'service', 'column' => 1, 'row' => 1],
                    ['id' => 'db', 'label' => 'Ledger DB', 'detail' => 'Postgres', 'kind' => 'store', 'column' => 2, 'row' => 1],
                ],
                'edges' => [['from' => 'api', 'to' => 'db', 'label' => 'SQL']],
            ],
            'metrics' => [['value' => '12 min', 'label' => 'close time', 'detail' => 'from 9 hours']],
            'links' => [['label' => 'Write-up', 'url' => 'https://example.com', 'kind' => 'writeup']],
            'cover' => UploadedFile::fake()->image('cover.jpg', 1280, 720),
            'cover_alt' => 'Two ledger sheets',
            'galleryItems' => [
                ['image' => [UploadedFile::fake()->image('g1.jpg', 800, 600)], 'alt' => 'Balance scale', 'caption' => 'Every journal balances.'],
            ],
            'is_published' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $undo();
    $project = Project::query()->firstOrFail();

    expect($project->slug)->toBe('ledgerline')
        ->and($project->stack)->toBe(['PostgreSQL', 'Go'])
        ->and($project->overview)->toBe(['Tessellate moves money.'])
        ->and($project->architecture['edges'][0]['to'] ?? null)->toBe('db')
        ->and($project->metrics[0]['value'])->toBe('12 min')
        ->and($project->hasMedia('cover'))->toBeTrue()
        ->and($project->galleryItems)->toHaveCount(1)
        ->and($project->galleryItems->first()?->hasMedia('image'))->toBeTrue()
        ->and($project->isPublished())->toBeTrue();
});

test('projects can be filtered and drafts are marked', function () {
    $live = Project::factory()->create(['category' => ProjectCategory::Platform]);
    $draft = Project::factory()->draft()->create(['category' => ProjectCategory::Product]);

    Livewire::test(ListProjects::class)
        ->assertCanSeeTableRecords([$live, $draft])
        ->filterTable('category', ProjectCategory::Platform->value)
        ->assertCanSeeTableRecords([$live])
        ->assertCanNotSeeTableRecords([$draft]);
});

test('a project can be edited and soft deleted', function () {
    $project = Project::factory()->create();

    Livewire::test(EditProject::class, ['record' => $project->getRouteKey()])
        ->fillForm(['tagline' => 'New tagline'])
        ->call('save')
        ->assertHasNoFormErrors()
        ->callAction('delete');

    expect(Project::withTrashed()->find($project->id)?->tagline)->toBe('New tagline')
        ->and(Project::query()->count())->toBe(0);
});

test('a company lists its projects in a relation manager', function () {
    $company = Company::factory()->create();
    $project = Project::factory()->for($company)->create();

    Livewire::test(ProjectsRelationManager::class, ['ownerRecord' => $company, 'pageClass' => EditCompany::class])
        ->assertOk()
        ->assertCanSeeTableRecords([$project]);
});
