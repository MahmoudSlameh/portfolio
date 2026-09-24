<?php

use App\Enums\BookCoverStyle;
use App\Enums\ReadingStatus;
use App\Enums\SocialPlatform;
use App\Enums\UsesKind;
use App\Filament\Pages\EditNowPage;
use App\Filament\Resources\Books\Pages\CreateBook;
use App\Filament\Resources\Books\Pages\ListBooks;
use App\Filament\Resources\Socials\Pages\ManageSocials;
use App\Filament\Resources\UsesGroups\Pages\CreateUsesGroup;
use App\Models\Book;
use App\Models\NowPage;
use App\Models\Social;
use App\Models\UsesGroup;
use Filament\Forms\Components\Repeater;
use Livewire\Livewire;

beforeEach(fn () => actingAsAdmin());

test('a finished book with a rating and a generated cover can be added', function () {
    Livewire::test(CreateBook::class)
        ->assertSee('Book title')
        ->fillForm([
            'title' => 'Designing Data-Intensive Applications',
            'author' => 'Martin Kleppmann',
            'status' => ReadingStatus::Read->value,
            'finished_at' => '2025-11-20',
            'rating' => 5,
            'cover_style' => BookCoverStyle::Circle->value,
            'cover_background' => '#112233',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $book = Book::query()->firstOrFail();

    expect($book->slug)->toBe('designing-data-intensive-applications')
        ->and($book->finished_at?->format('Y-m-d'))->toBe('2025-11-01')
        ->and($book->rating)->toBe(5)
        ->and($book->cover_style)->toBe(BookCoverStyle::Circle);
});

test('books can be filtered by status', function () {
    $reading = Book::factory()->reading()->create();
    $read = Book::factory()->create();

    Livewire::test(ListBooks::class)
        ->filterTable('status', ReadingStatus::Reading->value)
        ->assertCanSeeTableRecords([$reading])
        ->assertCanNotSeeTableRecords([$read]);
});

test('a uses group is created with its items in order', function () {
    $undo = Repeater::fake();

    Livewire::test(CreateUsesGroup::class)
        ->fillForm([
            'title' => 'Hardware',
            'kind' => UsesKind::Hardware->value,
            'items' => [
                ['name' => 'MacBook Pro', 'description' => 'Fast.', 'url' => null],
                ['name' => 'HHKB', 'description' => 'Clicky.', 'url' => 'https://hhkb.example'],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $undo();

    expect(UsesGroup::query()->firstOrFail()->items->pluck('name')->all())->toBe(['MacBook Pro', 'HHKB']);
});

test('social links get a label from the platform', function () {
    Livewire::test(ManageSocials::class)
        ->mountAction('create')
        ->fillForm(['platform' => SocialPlatform::Github->value])
        ->assertSchemaStateSet(['label' => 'GitHub'])
        ->fillForm(['url' => 'https://github.com/mahmoudslameh', 'handle' => '@mahmoudslameh'])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    expect(Social::query()->firstOrFail()->label)->toBe('GitHub');
});

test('the now page saves focus, learning and picked books', function () {
    $undo = Repeater::fake();
    $book = Book::factory()->reading()->create();

    Livewire::test(EditNowPage::class)
        ->fillForm([
            'location' => 'Damascus',
            'focus' => [['title' => 'Portfolio v2', 'body' => 'Shipping the new site.']],
            'learning' => [['title' => 'Rust', 'body' => 'Slowly.']],
            'readingBooks' => [$book->id],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $undo();
    $now = NowPage::current();

    expect($now->location)->toBe('Damascus')
        ->and($now->focus)->toBe([['title' => 'Portfolio v2', 'body' => 'Shipping the new site.']])
        ->and($now->readingBooks->pluck('id')->all())->toBe([$book->id]);
});
