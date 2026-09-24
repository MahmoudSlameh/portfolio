<?php

use App\Filament\Resources\SkillCategories\Pages\ManageSkillCategories;
use App\Filament\Resources\Skills\Pages\ManageSkills;
use App\Models\Experience;
use App\Models\Skill;
use App\Models\SkillCategory;
use Livewire\Livewire;

beforeEach(fn () => actingAsAdmin());

test('skills are listed grouped by category with their usage', function () {
    $skill = Skill::factory()->create(['name' => 'Laravel']);
    Skill::factory()->stackOnly()->create(['name' => 'Redis']);
    Experience::factory()->create()->syncSkillsInOrder([$skill->id]);

    Livewire::test(ManageSkills::class)
        ->assertCanSeeTableRecords(Skill::all())
        ->assertSee('Stack only')
        ->assertSee('1 role');
});

test('a skill can be created in a category and its proficiency changed inline', function () {
    $category = SkillCategory::factory()->create();

    Livewire::test(ManageSkills::class)
        ->callAction('create', data: ['name' => 'Laravel', 'skill_category_id' => $category->id, 'proficiency' => 5, 'years' => 8])
        ->assertHasNoFormErrors();

    $skill = Skill::query()->where('name', 'Laravel')->firstOrFail();

    expect($skill->slug)->toBe('laravel')->and($skill->proficiency)->toBe(5);

    Livewire::test(ManageSkills::class)->call('updateTableColumnState', 'proficiency', (string) $skill->getKey(), 3);

    expect($skill->fresh()?->proficiency)->toBe(3);
});

test('skill names are unique', function () {
    Skill::factory()->create(['name' => 'Laravel']);

    Livewire::test(ManageSkills::class)
        ->callAction('create', data: ['name' => 'Laravel'])
        ->assertHasFormErrors(['name' => 'unique']);
});

test('categories show their skill count', function () {
    $category = SkillCategory::factory()->has(Skill::factory()->count(2))->create();

    Livewire::test(ManageSkillCategories::class)
        ->assertCanSeeTableRecords([$category])
        ->callAction('create', data: ['name' => 'Tooling'])
        ->assertHasNoFormErrors();

    expect(SkillCategory::query()->where('slug', 'tooling')->exists())->toBeTrue();
});
