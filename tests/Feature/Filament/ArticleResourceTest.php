<?php

use App\Enums\ArticleStatus;
use App\Filament\Resources\Articles\Pages\CreateArticle;
use App\Filament\Resources\Articles\Pages\EditArticle;
use App\Filament\Resources\Articles\Pages\ListArticles;
use App\Models\Article;
use App\Models\Project;
use App\Support\Content\PortfolioContent;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Repeater;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(fn () => actingAsAdmin());

test('an article with every text block can be written and published', function () {
    $undoBuilder = Builder::fake();
    $undoRepeater = Repeater::fake();
    $project = Project::factory()->create();

    Livewire::test(CreateArticle::class)
        ->fillForm([
            'title' => 'Ledgers are just logs',
            'excerpt' => 'What a ledger taught me.',
            'tags' => ['architecture', 'payments'],
            'status' => ArticleStatus::Published->value,
            'projects' => [$project->id],
            'body' => [
                ['type' => 'paragraph', 'data' => ['text' => 'Every engineer near money learns this.']],
                ['type' => 'heading', 'data' => ['text' => 'Start with invariants', 'id' => null]],
                ['type' => 'code', 'data' => ['language' => 'php', 'filename' => 'Journal.php', 'code' => '<?php echo 1;']],
                ['type' => 'quote', 'data' => ['text' => 'Balances are derived.', 'cite' => 'Me']],
                ['type' => 'list', 'data' => ['items' => [['item' => 'One'], ['item' => 'Two']]]],
                ['type' => 'callout', 'data' => ['title' => 'Note', 'text' => 'Keep journals append-only.']],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $undoBuilder();
    $undoRepeater();
    $article = Article::query()->firstOrFail();
    $detail = app(PortfolioContent::class)->articleBySlug('ledgers-are-just-logs');

    expect($article->published_at)->not->toBeNull()
        ->and($article->projects->pluck('id')->all())->toBe([$project->id])
        ->and(collect($article->body)->pluck('type')->all())->toBe(['paragraph', 'heading', 'code', 'quote', 'list', 'callout'])
        ->and($detail['body'][1] ?? null)->toBe(['type' => 'heading', 'id' => 'start-with-invariants', 'text' => 'Start with invariants'])
        ->and($detail['body'][4]['items'] ?? null)->toBe(['One', 'Two']);
});

test('an uploaded body image can be placed with an image block', function () {
    Storage::fake('public');
    $undoBuilder = Builder::fake();
    $article = Article::factory()->create(['body' => []]);
    $media = $article->addMedia(UploadedFile::fake()->image('diagram.png', 1200, 800))->toMediaCollection('body_images');

    Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
        ->fillForm(['body' => [['type' => 'image', 'data' => ['media_uuid' => $media->uuid, 'alt' => 'Write path diagram', 'caption' => 'The write path']]]])
        ->call('save')
        ->assertHasNoFormErrors();

    $undoBuilder();
    $block = app(PortfolioContent::class)->articleBySlug($article->slug)['body'][0] ?? null;

    expect($block['type'] ?? null)->toBe('image')
        ->and($block['image']['alt'] ?? null)->toBe('Write path diagram')
        ->and($block['caption'] ?? null)->toBe('The write path');
});

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
