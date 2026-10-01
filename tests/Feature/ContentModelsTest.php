<?php

use App\Enums\ArticleStatus;
use App\Enums\ContactTopic;
use App\Models\Article;
use App\Models\Book;
use App\Models\ContactMessage;
use App\Models\NowPage;
use App\Models\Project;
use App\Models\Social;
use App\Models\UsesGroup;
use App\Models\UsesItem;
use App\Support\Content\ArticleDocument;

test('reading time matches the reference frontend for the ledger article', function () {
    $fixture = json_decode((string) file_get_contents(base_path('tests/Fixtures/ledgers-article.json')), true);

    $article = Article::factory()->make(['body' => ArticleDocument::fromBuilder($fixture['body'])]);

    expect($article->wordCount())->toBe($fixture['expectedWords'])
        ->and($article->readingMinutes())->toBe($fixture['expectedMinutes']);
});

test('reading time is at least one minute', function () {
    expect(Article::factory()->make(['body' => ArticleDocument::empty()])->readingMinutes())->toBe(1);
});

test('publishing an article without a date stamps it now', function () {
    $article = Article::factory()->draft()->create();

    expect($article->published_at)->toBeNull();

    $article->update(['status' => ArticleStatus::Published]);

    expect($article->published_at)->not->toBeNull()
        ->and($article->isPublished())->toBeTrue();
});

test('only published, non-scheduled articles are public', function () {
    $live = Article::factory()->create();
    Article::factory()->draft()->create();
    Article::factory()->create(['published_at' => now()->addDay()]);

    expect(Article::query()->published()->pluck('id')->all())->toBe([$live->id]);
});

test('articles and projects are related both ways', function () {
    $article = Article::factory()->create(['title' => 'Ledgers are just logs', 'slug' => null]);
    $project = Project::factory()->create();

    $article->projects()->attach($project);

    expect($article->slug)->toBe('ledgers-are-just-logs')
        ->and($project->articles()->pluck('articles.id')->all())->toBe([$article->id]);
});

test('the now page lists picked books in order, falling back to books being read', function () {
    $reading = Book::factory()->reading()->create(['title' => 'B']);
    Book::factory()->reading()->create(['title' => 'A', 'is_visible' => false]);
    Book::factory()->create();

    $now = NowPage::current();

    expect($now->currentlyReading()->pluck('id')->all())->toBe([$reading->id]);

    $first = Book::factory()->create();
    $second = Book::factory()->create();
    $now->readingBooks()->attach([$second->id => ['sort_order' => 1], $first->id => ['sort_order' => 0]]);

    expect($now->currentlyReading()->pluck('id')->all())->toBe([$first->id, $second->id]);
});

test('uses items belong to an ordered group', function () {
    $group = UsesGroup::factory()->create();
    $second = UsesItem::factory()->for($group, 'group')->create(['sort_order' => 2]);
    $first = UsesItem::factory()->for($group, 'group')->create(['sort_order' => 1]);

    expect($group->items->pluck('id')->all())->toBe([$first->id, $second->id]);

    $group->delete();

    expect(UsesItem::query()->count())->toBe(0);
});

test('contact messages can be marked read and unread', function () {
    $message = ContactMessage::factory()->create(['topic' => ContactTopic::Role]);
    ContactMessage::factory()->read()->create();

    expect(ContactMessage::query()->unread()->count())->toBe(1);

    $message->markAsRead();

    expect(ContactMessage::query()->unread()->count())->toBe(0);

    $message->markAsUnread();

    expect($message->read_at)->toBeNull();
});

test('socials are visible and ordered', function () {
    $second = Social::factory()->create(['sort_order' => 2]);
    $first = Social::factory()->create(['sort_order' => 1]);
    Social::factory()->create(['is_visible' => false]);

    expect(Social::query()->visible()->ordered()->pluck('id')->all())->toBe([$first->id, $second->id]);
});
