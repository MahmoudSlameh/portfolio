<?php

use App\Models\Article;
use App\Models\Book;
use App\Models\Certification;
use App\Models\Company;
use App\Models\Education;
use App\Models\Experience;
use App\Models\NowPage;
use App\Models\Profile;
use App\Models\Project;
use App\Models\ProjectGalleryItem;
use App\Models\Skill;
use App\Models\Social;
use App\Models\Testimonial;
use App\Models\UsesItem;
use App\Support\Content\PortfolioContent;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');

    Profile::current()->update(Profile::factory()->raw());
    $company = Company::factory()->create();
    $company->addMedia(UploadedFile::fake()->image('logo.png', 400, 200))->toMediaCollection('logo');
    $experience = Experience::factory()->for($company)->current()->create();
    $experience->syncSkillsInOrder([Skill::factory()->create()->id]);

    $project = Project::factory()->for($company)->for($experience)->featured()->create();
    $project->addMedia(UploadedFile::fake()->image('cover.jpg', 1280, 720))->toMediaCollection('cover');
    ProjectGalleryItem::factory()->for($project)->create()
        ->addMedia(UploadedFile::fake()->image('g.jpg', 800, 600))->toMediaCollection('image');

    $article = Article::factory()->create();
    $article->projects()->attach($project);
    Education::factory()->create();
    Certification::factory()->create();
    Testimonial::factory()->for($company)->create();
    Social::factory()->create();
    UsesItem::factory()->create();
    Book::factory()->reading()->create();
    Book::factory()->create();
    NowPage::current()->update(NowPage::factory()->raw());

    $this->content = app(PortfolioContent::class);
});

test('profile and socials match their interfaces', function () {
    expect($this->content->profile())->toMatchInterface('Profile')
        ->and($this->content->socials()[0])->toMatchInterface('Social');
});

test('skills, companies, career, testimonials, education and certifications match their interfaces', function () {
    $group = $this->content->skillGroups()[0];

    expect($group)->toMatchInterface('SkillGroup')
        ->and($group['category'])->toMatchInterface('SkillCategory')
        ->and($group['skills'][0])->toMatchInterface('Skill')
        ->and($this->content->companies()[0])->toMatchInterface('Company')
        ->and($this->content->career()[0])->toMatchInterface('CareerEntry')
        ->and($this->content->career()[0]['company'])->toMatchInterface('Company')
        ->and($this->content->testimonials()[0])->toMatchInterface('TestimonialEntry')
        ->and($this->content->education()[0])->toMatchInterface('Education')
        ->and($this->content->certifications()[0])->toMatchInterface('Certification');
});

test('projects match their interfaces, including images', function () {
    $project = $this->content->projects()[0];
    $detail = $this->content->projectBySlug($project['slug']);

    expect($project)->toMatchInterface('Project')
        ->and($project['cover'])->toMatchInterface('ImageData')
        ->and($project['gallery'][0])->toHaveKey('caption')
        ->and($project['metrics'][0])->toMatchInterface('ProjectMetric')
        ->and($this->content->projectFacets())->toMatchInterface('ProjectFacets')
        ->and($detail)->toMatchInterface('ProjectDetail')
        ->and($detail['experience'] ?? null)->toMatchInterface('Experience')
        ->and($detail['relatedArticles'][0] ?? null)->toMatchInterface('ArticleSummary');
});

test('articles match their interfaces', function () {
    $summary = $this->content->articles()[0];

    expect($summary)->toMatchInterface('ArticleSummary')
        ->and($this->content->articleBySlug($summary['slug']))->toMatchInterface('ArticleDetail');
});

test('books, uses, now and the search index match their interfaces', function () {
    expect($this->content->books()[0])->toMatchInterface('Book')
        ->and($this->content->bookStats())->toMatchInterface('BookStats')
        ->and($this->content->uses()[0])->toMatchInterface('UsesGroup')
        ->and($this->content->uses()[0]['items'][0])->toMatchInterface('UsesItem')
        ->and($this->content->now())->toMatchInterface('NowDetail')
        ->and($this->content->searchIndex())->toMatchInterface('SearchIndex');
});

test('images carry dimensions, srcset and alt text', function () {
    $cover = $this->content->projects()[0]['cover'];

    expect($cover['width'])->toBe(1280)
        ->and($cover['height'])->toBe(720)
        ->and($cover['src'])->toContain('.webp')
        ->and($cover['srcSet'])->not->toBeEmpty()
        ->and($cover['alt'])->not->toBeEmpty();
});
