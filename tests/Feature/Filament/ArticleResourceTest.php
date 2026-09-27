<?php

use App\Enums\ArticleStatus;
use App\Filament\Resources\Articles\Pages\CreateArticle;
use App\Filament\Resources\Articles\Pages\EditArticle;
use App\Filament\Resources\Articles\Pages\ListArticles;
use App\Models\Article;
use App\Models\Project;
use App\Support\Content\ArticleDocument;
use App\Support\Content\PortfolioContent;
use Filament\Actions\Testing\TestAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(fn () => actingAsAdmin());

test('an article written in the rich editor is published as blocks', function () {
    $project = Project::factory()->create();

    Livewire::test(CreateArticle::class)
        ->fillForm([
            'title' => 'Ledgers are just logs',
            'excerpt' => 'What a ledger taught me.',
            'tags' => ['architecture', 'payments'],
            'status' => ArticleStatus::Published->value,
            'projects' => [$project->id],
            'body' => articleDoc(
                ['type' => 'paragraph', 'content' => [
                    ['type' => 'text', 'text' => 'Every engineer near '],
                    ['type' => 'text', 'text' => 'money', 'marks' => [['type' => 'bold']]],
                    ['type' => 'text', 'text' => ' learns this.'],
                ]],
                ['type' => 'heading', 'attrs' => ['level' => 2], 'content' => [['type' => 'text', 'text' => 'Start with invariants']]],
                ['type' => 'codeBlock', 'attrs' => ['language' => 'php'], 'content' => [['type' => 'text', 'text' => '<?php echo 1;']]],
                ['type' => 'blockquote', 'content' => [
                    ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Balances are derived.']]],
                    ['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => '— Me']]],
                ]],
                ['type' => 'bulletList', 'content' => [
                    ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'One']]]]],
                    ['type' => 'listItem', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Two']]]]],
                ]],
                ['type' => 'customBlock', 'attrs' => ['id' => 'callout', 'config' => ['title' => 'Note', 'text' => 'Keep journals append-only.']]],
                ['type' => 'customBlock', 'attrs' => ['id' => 'code', 'config' => ['language' => 'ts', 'filename' => 'journal.ts', 'code' => 'const total = 0;']]],
            ),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $article = Article::query()->firstOrFail();
    $detail = app(PortfolioContent::class)->articleBySlug('ledgers-are-just-logs');

    expect($article->published_at)->not->toBeNull()
        ->and($article->projects->pluck('id')->all())->toBe([$project->id])
        ->and($article->body['type'] ?? null)->toBe('doc')
        ->and($detail['body'] ?? null)->toBe([
            ['type' => 'paragraph', 'text' => 'Every engineer near **money** learns this.'],
            ['type' => 'heading', 'id' => 'start-with-invariants', 'text' => 'Start with invariants'],
            ['type' => 'code', 'language' => 'php', 'code' => '<?php echo 1;'],
            ['type' => 'quote', 'text' => 'Balances are derived.', 'cite' => 'Me'],
            ['type' => 'list', 'items' => ['One', 'Two']],
            ['type' => 'callout', 'title' => 'Note', 'text' => 'Keep journals append-only.'],
            ['type' => 'code', 'language' => 'ts', 'filename' => 'journal.ts', 'code' => 'const total = 0;'],
        ]);
});

test('an image in the body is served with its alt text and caption', function () {
    Storage::fake('public');
    $article = Article::factory()->create(['body' => ArticleDocument::empty()]);
    $media = $article->addMedia(UploadedFile::fake()->image('diagram.png', 1200, 800))->toMediaCollection('body_images');

    Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
        ->fillForm(['body' => articleDoc(['type' => 'image', 'attrs' => ['id' => $media->uuid, 'alt' => 'Write path diagram', 'title' => 'The write path']])])
        ->call('save')
        ->assertHasNoFormErrors();

    $block = app(PortfolioContent::class)->articleBySlug($article->slug)['body'][0] ?? null;

    expect($block['type'] ?? null)->toBe('image')
        ->and($block['image']['alt'] ?? null)->toBe('Write path diagram')
        ->and($block['caption'] ?? null)->toBe('The write path');
});

test('images taken out of the body are removed on save', function () {
    Storage::fake('public');
    $article = Article::factory()->create(['body' => ArticleDocument::empty()]);
    $kept = $article->addMedia(UploadedFile::fake()->image('kept.png'))->toMediaCollection('body_images');
    $article->addMedia(UploadedFile::fake()->image('removed.png'))->toMediaCollection('body_images');

    Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
        ->fillForm(['body' => articleDoc(['type' => 'image', 'attrs' => ['id' => $kept->uuid, 'alt' => 'Kept']])])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($article->fresh()?->getMedia('body_images')->pluck('uuid')->all())->toBe([$kept->uuid]);
});

test('markdown can be imported into the body', function (string $mode, int $blocks) {
    $article = Article::factory()->create(['body' => ArticleDocument::fromBuilder([
        ['type' => 'paragraph', 'data' => ['text' => 'Already here.']],
    ])]);

    $markdown = <<<'MD'
        ## Why logs

        Balances are **derived**, see [the post](https://example.com/post) and `sum()`.

        - append only
        - replayable

        > Keep it boring.

        ```php
        echo 1;
        ```

        <script>alert(1)</script>
        MD;

    Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
        ->callAction(TestAction::make('importMarkdown')->schemaComponent('body'), data: ['markdown' => $markdown, 'mode' => $mode])
        ->call('save')
        ->assertHasNoFormErrors();

    $body = app(PortfolioContent::class)->articleBySlug($article->slug)['body'] ?? [];
    $imported = array_slice($body, -5);

    expect($body)->toHaveCount($blocks)
        ->and($imported)->toBe([
            ['type' => 'heading', 'id' => 'why-logs', 'text' => 'Why logs'],
            ['type' => 'paragraph', 'text' => 'Balances are **derived**, see [the post](https://example.com/post) and `sum()`.'],
            ['type' => 'list', 'items' => ['append only', 'replayable']],
            ['type' => 'quote', 'text' => 'Keep it boring.'],
            ['type' => 'code', 'language' => 'php', 'code' => "echo 1;\n"],
        ])
        ->and(json_encode($body))->not->toContain('script');
})->with([
    'appended' => ['append', 6],
    'replacing the body' => ['replace', 5],
]);

test('the list separates published articles and drafts', function () {
    $published = Article::factory()->create();
    $draft = Article::factory()->draft()->create();

    Livewire::test(ListArticles::class)
        ->assertCanSeeTableRecords([$published, $draft])
        ->set('activeTab', 'drafts')
        ->assertCanSeeTableRecords([$draft])
        ->assertCanNotSeeTableRecords([$published]);
});

test('the title is required', function () {
    Livewire::test(CreateArticle::class)
        ->fillForm(['title' => ''])
        ->call('create')
        ->assertHasFormErrors(['title' => 'required']);
});

/**
 * @param  array<string, mixed>  ...$nodes
 * @return array{type: 'doc', content: list<array<string, mixed>>}
 */
function articleDoc(array ...$nodes): array
{
    return ['type' => 'doc', 'content' => array_values($nodes)];
}
