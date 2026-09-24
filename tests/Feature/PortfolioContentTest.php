<?php

use App\Enums\BookCategory;
use App\Enums\ProjectCategory;
use App\Models\Article;
use App\Models\Book;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Project;
use App\Models\Skill;
use App\Support\Content\PortfolioContent;

beforeEach(function () {
    $this->content = app(PortfolioContent::class);
});

test('career lists visible roles newest first with changelog metadata', function () {
    $old = Experience::factory()->create(['start_date' => '2016-09-01']);
    $new = Experience::factory()->current()->create(['start_date' => '2024-02-01']);
    Experience::factory()->create(['is_visible' => false]);

    $career = $this->content->career();

    expect(array_column($career, 'id'))->toBe([(string) $new->id, (string) $old->id])
        ->and($career[1]['version'])->toBe('v1.0.0')
        ->and($career[1]['message'])->toBe('init: first commit')
        ->and($career[0]['version'])->toBe('v2.0.0')
        ->and($career[0]['end'])->toBeNull()
        ->and($career[0]['start'])->toBe('2024-02');
});

test('career entries only reference published projects', function () {
    $experience = Experience::factory()->create();
    $live = Project::factory()->for($experience)->create();
    Project::factory()->for($experience)->draft()->create();

    $entry = $this->content->career()[0];

    expect($entry['projectIds'])->toBe([$live->slug])
        ->and($entry['projects'])->toBe([['slug' => $live->slug, 'title' => $live->title]]);
});

test('projects are filtered by featured, category, technology and search', function () {
    $go = Skill::factory()->create(['name' => 'Go']);
    $ledger = Project::factory()->featured()->create(['title' => 'Ledgerline', 'category' => ProjectCategory::Platform, 'year' => 2025]);
    $ledger->syncSkillsInOrder([$go->id]);
    Project::factory()->create(['title' => 'Atlas', 'category' => ProjectCategory::DesignSystem, 'year' => 2023, 'tagline' => 'Tokens café']);
    Project::factory()->draft()->create(['title' => 'Secret']);

    $titles = fn (array $filters): array => array_column($this->content->projects($filters), 'title');

    expect($titles([]))->toBe(['Ledgerline', 'Atlas'])
        ->and($titles(['sort' => 'oldest']))->toBe(['Atlas', 'Ledgerline'])
        ->and($titles(['featured' => true]))->toBe(['Ledgerline'])
        ->and($titles(['category' => 'design-system']))->toBe(['Atlas'])
        ->and($titles(['tech' => 'Go']))->toBe(['Ledgerline'])
        ->and($titles(['search' => 'CAFE']))->toBe(['Atlas'])
        ->and($this->content->projectFacets())->toBe(['technologies' => ['Go'], 'categories' => ['platform', 'design-system']]);
});

test('a case study links to the previous and next project by year', function () {
    $newest = Project::factory()->create(['year' => 2025]);
    $middle = Project::factory()->create(['year' => 2023]);
    $oldest = Project::factory()->create(['year' => 2020]);

    $detail = $this->content->projectBySlug($middle->slug);

    expect($detail['previous'])->toBe(['slug' => $newest->slug, 'title' => $newest->title])
        ->and($detail['next'])->toBe(['slug' => $oldest->slug, 'title' => $oldest->title])
        ->and($this->content->projectBySlug('missing'))->toBeNull();
});

test('drafts are never returned', function () {
    $draft = Project::factory()->draft()->create();
    $article = Article::factory()->draft()->create();

    expect($this->content->projectBySlug($draft->slug))->toBeNull()
        ->and($this->content->articleBySlug($article->slug))->toBeNull()
        ->and($this->content->searchIndex()['projects'])->toBe([]);
});

test('articles are newest first, filterable and linked to their neighbours', function () {
    $old = Article::factory()->create(['title' => 'Old', 'published_at' => now()->subYear(), 'tags' => ['react']]);
    $new = Article::factory()->create(['title' => 'New', 'published_at' => now()->subDay(), 'tags' => ['payments']]);

    expect(array_column($this->content->articles(), 'title'))->toBe(['New', 'Old'])
        ->and(array_column($this->content->articles(['tag' => 'react']), 'title'))->toBe(['Old'])
        ->and(array_column($this->content->articles(['search' => 'new']), 'title'))->toBe(['New'])
        ->and($this->content->articleTags())->toBe(['payments', 'react']);

    $detail = $this->content->articleBySlug($old->slug);

    expect($detail['next']['slug'] ?? null)->toBe($new->slug)
        ->and($detail['previous'])->toBeNull()
        ->and(collect($detail['body'])->pluck('type')->all())->toBe(['paragraph', 'heading', 'code', 'list', 'callout', 'quote'])
        ->and($detail['body'][1])->toBe(['type' => 'heading', 'id' => 'start-with-invariants', 'text' => 'Start with invariants']);
});

test('books are ordered reading, read (latest first), queued and have stats', function () {
    $queued = Book::factory()->toRead()->create();
    $older = Book::factory()->create(['finished_at' => '2023-05-01', 'rating' => 4, 'pages' => 100, 'category' => BookCategory::Design]);
    $reading = Book::factory()->reading()->create();
    $newer = Book::factory()->create(['finished_at' => '2025-02-01', 'rating' => 5, 'pages' => 300, 'category' => BookCategory::Systems]);

    expect(array_column($this->content->books(), 'slug'))->toBe([$reading->slug, $newer->slug, $older->slug, $queued->slug])
        ->and(array_column($this->content->books(['status' => 'read']), 'slug'))->toBe([$newer->slug, $older->slug]);

    expect($this->content->bookStats())->toMatchArray([
        'total' => 4,
        'read' => 2,
        'reading' => 1,
        'queued' => 1,
        'pagesRead' => 400,
        'averageRating' => 4.5,
        'perYear' => [['year' => 2023, 'count' => 1], ['year' => 2024, 'count' => 0], ['year' => 2025, 'count' => 1]],
    ]);
});

test('book stats work with an empty shelf', function () {
    expect($this->content->bookStats())->toMatchArray(['total' => 0, 'averageRating' => 0.0, 'perYear' => []]);
});

test('current studies come first in education', function () {
    $finished = Education::factory()->create(['end_date' => '2022-06-01']);
    $current = Education::factory()->current()->create();

    expect(array_column($this->content->education(), 'id'))->toBe([(string) $current->id, (string) $finished->id]);
});
