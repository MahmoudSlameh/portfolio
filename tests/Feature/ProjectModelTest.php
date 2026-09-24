<?php

use App\Enums\ProjectStatus;
use App\Models\Company;
use App\Models\Experience;
use App\Models\Project;
use App\Models\ProjectGalleryItem;
use App\Models\Skill;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('the factory builds a complete case study', function () {
    $project = Project::factory()->create();

    expect($project->status)->toBeInstanceOf(ProjectStatus::class)
        ->and($project->architecture['nodes'] ?? [])->toHaveCount(3)
        ->and($project->architecture['edges'] ?? [])->toHaveCount(2)
        ->and($project->approach[0])->toHaveKeys(['title', 'description'])
        ->and($project->metrics[0])->toHaveKeys(['value', 'label', 'detail'])
        ->and($project->links[0])->toHaveKeys(['label', 'url', 'kind']);
});

test('the slug comes from the title and is the route key', function () {
    $project = Project::factory()->create(['title' => 'Ledgerline', 'slug' => null]);

    expect($project->slug)->toBe('ledgerline')
        ->and($project->getRouteKey())->toBe('ledgerline');
});

test('only published projects that are not scheduled are public', function () {
    $live = Project::factory()->create();
    Project::factory()->draft()->create();
    $scheduled = Project::factory()->scheduled()->create();

    expect(Project::query()->published()->pluck('id')->all())->toBe([$live->id])
        ->and($live->isPublished())->toBeTrue()
        ->and($scheduled->isPublished())->toBeFalse();
});

test('a project links to its company, experience and ordered stack', function () {
    $company = Company::factory()->create();
    $experience = Experience::factory()->for($company)->create();
    $project = Project::factory()->for($company)->for($experience)->featured()->create();
    $project->syncSkillsInOrder([
        Skill::factory()->create(['name' => 'Go'])->id,
        Skill::factory()->create(['name' => 'Kafka'])->id,
    ]);

    expect($project->company?->is($company))->toBeTrue()
        ->and($experience->projects()->pluck('id')->all())->toBe([$project->id])
        ->and($company->projects()->count())->toBe(1)
        ->and(Project::query()->featured()->count())->toBe(1)
        ->and($project->fresh()?->stack)->toBe(['Go', 'Kafka']);
});

test('gallery items keep their order and each carries its own image, alt and caption', function () {
    Storage::fake('public');
    $project = Project::factory()->create();
    $second = ProjectGalleryItem::factory()->for($project)->create(['sort_order' => 2, 'caption' => 'Second']);
    $first = ProjectGalleryItem::factory()->for($project)->create(['sort_order' => 1, 'caption' => 'First']);
    $first->addMedia(UploadedFile::fake()->image('first.png', 1152, 864))->toMediaCollection('image');

    expect($project->galleryItems->pluck('id')->all())->toBe([$first->id, $second->id])
        ->and($first->getFirstMedia('image')?->getCustomProperty('width'))->toBe(1152);

    $this->assertDatabaseHas('media', ['model_type' => 'project_gallery_item', 'model_id' => $first->id]);
});

test('soft-deleting a project hides it but keeps its gallery', function () {
    $project = Project::factory()->create();
    ProjectGalleryItem::factory()->for($project)->create();

    $project->delete();

    expect(Project::query()->count())->toBe(0)
        ->and(Project::withTrashed()->count())->toBe(1)
        ->and(ProjectGalleryItem::query()->count())->toBe(1);
});
