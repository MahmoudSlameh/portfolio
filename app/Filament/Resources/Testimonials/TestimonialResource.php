<?php

namespace App\Filament\Resources\Testimonials;

use App\Filament\Resources\Companies\Schemas\CompanyForm;
use App\Filament\Resources\Experiences\Schemas\ExperienceForm;
use App\Filament\Resources\Testimonials\Pages\ManageTestimonials;
use App\Filament\Support\Columns;
use App\Filament\Support\Fields;
use App\Models\Company;
use App\Models\Testimonial;
use App\Support\Media\InitialsAvatar;
use App\Support\Media\MimeTypes;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Career';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'author_name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Textarea::make('quote')->required()->rows(4)->maxLength(1000)->columnSpanFull(),
                TextInput::make('author_name')->label('Author')->required()->maxLength(255),
                TextInput::make('author_role')->label('Author role')->placeholder('VP Engineering')->maxLength(255),
                Select::make('company_id')
                    ->label('Company')
                    ->relationship('company', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Company $company): string => ExperienceForm::companyLabel($company))
                    ->allowHtml()
                    ->searchable(['name'])
                    ->preload()
                    ->createOptionForm(CompanyForm::quick()),
                TextInput::make('relation')->placeholder('Managed me, 2024 — now')->maxLength(255),
                Fields::image('avatar', ['1:1'], MimeTypes::RASTER)->label('Photo')->avatar(),
                Toggle::make('is_visible')->label('Show on the site')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('avatar')
                    ->label('')
                    ->collection('avatar')
                    ->conversion('thumb')
                    ->circular()
                    ->imageSize(40)
                    ->defaultImageUrl(fn (Testimonial $record): string => InitialsAvatar::dataUri($record->author_name)),
                TextColumn::make('author_name')
                    ->label('Author')
                    ->weight('medium')
                    ->description(fn (Testimonial $record): string => collect([$record->author_role, $record->company?->name])->filter()->implode(' · '))
                    ->searchable(),
                TextColumn::make('quote')->limit(90)->wrap()->color('gray'),
                Columns::visibility(),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon(Heroicon::OutlinedChatBubbleLeftRight);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTestimonials::route('/'),
        ];
    }
}
