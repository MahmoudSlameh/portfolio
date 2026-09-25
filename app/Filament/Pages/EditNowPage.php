<?php

namespace App\Filament\Pages;

use App\Filament\Support\SingletonPage;
use App\Models\Book;
use App\Models\NowPage;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * The /now page: what the owner is focused on, learning and reading right now.
 */
class EditNowPage extends SingletonPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|UnitEnum|null $navigationGroup = 'Profile';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Now page';

    protected static ?string $title = 'Now';

    protected static ?string $slug = 'now';

    public function getRecord(): NowPage
    {
        return NowPage::current();
    }

    public function getSubheading(): ?string
    {
        return 'Last updated '.($this->getRecord()->updated_at?->diffForHumans() ?? 'never').'.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')
                ->label('View /now')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(url('/now'), shouldOpenInNewTab: true),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->schema([
                TextInput::make('location')->placeholder('Damascus, with a week in Dubai each month')->maxLength(255),
                TextInput::make('availability')->placeholder('Open to one freelance project')->maxLength(255),
            ]),
            Section::make('Focus')->icon(Heroicon::OutlinedBolt)->schema([self::entries('focus', 'Add focus')]),
            Section::make('Learning')->icon(Heroicon::OutlinedAcademicCap)->schema([self::entries('learning', 'Add topic')]),
            Section::make('Reading')
                ->icon(Heroicon::OutlinedBookOpen)
                ->description('Leave empty to list every book marked "Reading".')
                ->schema([
                    Select::make('readingBooks')
                        ->hiddenLabel()
                        ->relationship('readingBooks', 'title')
                        ->getOptionLabelFromRecordUsing(fn (Book $book): string => "{$book->title} — {$book->author}")
                        ->multiple()
                        ->preload()
                        ->searchable(),
                ]),
        ]);
    }

    private static function entries(string $name, string $addLabel): Repeater
    {
        return Repeater::make($name)
            ->hiddenLabel()
            ->defaultItems(0)
            ->schema([
                TextInput::make('title')->required()->maxLength(255),
                Textarea::make('body')->rows(2)->required(),
            ])
            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
            ->collapsible()
            ->reorderable()
            ->addActionLabel($addLabel);
    }

    protected function afterSave(): void
    {
        $this->getRecord()->touch();
    }
}
