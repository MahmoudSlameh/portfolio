<?php

use App\Models\Company;
use App\Models\Experience;
use App\Models\Skill;
use App\Models\SkillCategory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('an experience stack keeps the chosen order', function () {
    $experience = Experience::factory()->create();
    [$go, $postgres, $kafka] = [
        Skill::factory()->create(['name' => 'Go']),
        Skill::factory()->stackOnly()->create(['name' => 'PostgreSQL']),
        Skill::factory()->create(['name' => 'Kafka']),
    ];

    $experience->syncSkillsInOrder([$kafka->id, $go->id, $postgres->id]);

    expect($experience->fresh()?->stack)->toBe(['Kafka', 'Go', 'PostgreSQL']);

    $experience->syncSkillsInOrder([$postgres->id, $kafka->id]);

    expect($experience->fresh()?->stack)->toBe(['PostgreSQL', 'Kafka']);
});

test('skills and categories get unique slugs', function () {
    $first = SkillCategory::factory()->create(['name' => 'Services & data', 'slug' => null]);
    $second = SkillCategory::factory()->create(['name' => 'Services & data', 'slug' => null]);

    expect($first->slug)->toBe('services-data')
        ->and($second->slug)->toBe('services-data-2')
        ->and(Skill::factory()->create(['name' => 'Next.js', 'slug' => null])->slug)->toBe('nextjs');
});

test('deleting a category keeps its skills as stack-only technologies', function () {
    $skill = Skill::factory()->create();

    $skill->category?->delete();

    expect($skill->fresh()?->skill_category_id)->toBeNull();
});

test('deleting a skill removes it from every stack', function () {
    $experience = Experience::factory()->create();
    $skill = Skill::factory()->create();
    $experience->syncSkillsInOrder([$skill->id]);

    $skill->delete();

    expect($experience->fresh()?->stack)->toBe([]);
});

test('polymorphic columns store short morph names', function () {
    Storage::fake('public');
    $company = Company::factory()->create();
    $company->addMedia(UploadedFile::fake()->image('logo.png'))->toMediaCollection('logo');

    $experience = Experience::factory()->create();
    $experience->syncSkillsInOrder([Skill::factory()->create()->id]);

    $this->assertDatabaseHas('media', ['model_type' => 'company', 'model_id' => $company->id]);
    $this->assertDatabaseHas('skillables', ['skillable_type' => 'experience', 'skillable_id' => $experience->id]);
});
