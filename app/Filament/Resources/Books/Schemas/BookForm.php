<?php

namespace App\Filament\Resources\Books\Schemas;

use App\Enums\BookCategory;
use App\Enums\BookCoverStyle;
use App\Enums\ReadingStatus;
use App\Filament\Support\Fields;
use App\Support\Media\MimeTypes;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class BookForm
{
    public static function configure(Schema $schema): Schema
    {
        $isRead = fn (Get $get): bool => $get('status') === ReadingStatus::Read->value || $get('status') === ReadingStatus::Read;

        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 3])->schema([
                Group::make([
                    Section::make('Book')->icon(Heroicon::OutlinedBookOpen)->columns(2)->schema([
                        Fields::slugSource('title', 'Title')->live(onBlur: true),
                        TextInput::make('author')->required()->maxLength(255)->live(onBlur: true),
                        Fields::slug('title'),
                        TextInput::make('published_year')->label('Published')->numeric()->minValue(1000)->maxValue((int) date('Y') + 1),
                        Select::make('category')->options(BookCategory::class)->default(BookCategory::Engineering->value)->required(),
                        TextInput::make('pages')->numeric()->minValue(1),
                    ]),
                    Section::make('Reading')->icon(Heroicon::OutlinedBookmark)->columns(2)->schema([
                        ToggleButtons::make('status')->options(ReadingStatus::class)->default(ReadingStatus::ToRead->value)->inline()->live()->required()->columnSpanFull(),
                        Fields::month('finished_at')->label('Finished')->visible($isRead),
                        ToggleButtons::make('rating')
                            ->options([1 => '★', 2 => '★★', 3 => '★★★', 4 => '★★★★', 5 => '★★★★★'])
                            ->inline()
                            ->visible($isRead),
                        Textarea::make('note')->rows(2)->maxLength(1000)->columnSpanFull(),
                    ]),
                ])->columnSpan(['lg' => 2]),
                Group::make([
                    Section::make('Cover')
                        ->icon(Heroicon::OutlinedSwatch)
                        ->description('Upload a real cover, or style the generated one.')
                        ->schema([
                            Fields::image('cover_image', ['2:3'], MimeTypes::RASTER)->label('Cover image'),
                            Text::make(fn (Get $get): HtmlString => new HtmlString(view('filament.components.book-cover-preview', [
                                'title' => $get('title') ?: 'Book title',
                                'author' => $get('author') ?: 'Author',
                                'background' => $get('cover_background') ?: '#1d2b3a',
                                'ink' => $get('cover_ink') ?: '#f1ede4',
                                'accent' => $get('cover_accent') ?: '#e8a33d',
                                'style' => $get('cover_style') instanceof BookCoverStyle ? $get('cover_style')->value : ($get('cover_style') ?: 'band'),
                            ])->render())),
                            Select::make('cover_style')->label('Pattern')->options(BookCoverStyle::class)->default(BookCoverStyle::Band->value)->live()->required(),
                            ColorPicker::make('cover_background')->label('Background')->default('#1d2b3a')->live()->required(),
                            ColorPicker::make('cover_ink')->label('Text')->default('#f1ede4')->live()->required(),
                            ColorPicker::make('cover_accent')->label('Accent')->default('#e8a33d')->live()->required(),
                        ]),
                    Section::make()->schema([
                        Toggle::make('is_visible')->label('Show on the site')->default(true),
                    ]),
                ])->columnSpan(['lg' => 1]),
            ]),
        ]);
    }
}
