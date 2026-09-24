<?php

namespace App\Filament\Resources\Skills;

use App\Filament\Resources\Skills\Pages\ManageSkills;
use App\Filament\Support\Columns;
use App\Filament\Support\Fields;
use App\Models\Skill;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class SkillResource extends Resource
{
    protected static ?string $model = Skill::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;

    protected static string|UnitEnum|null $navigationGroup = 'Work';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * @var array<int, string>
     */
    private const PROFICIENCY = [1 => '1 · Basic', 2 => '2 · Working', 3 => '3 · Solid', 4 => '4 · Advanced', 5 => '5 · Expert'];

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Fields::slugSource('name', 'Name')->unique(ignoreRecord: true)->placeholder('Laravel'),
                Fields::slug('name'),
                Select::make('skill_category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->preload()
                    ->createOptionForm([TextInput::make('name')->required()->maxLength(255)])
                    ->helperText('Without a category the technology is only used as a stack tag on roles and projects.')
                    ->columnSpanFull(),
                ToggleButtons::make('proficiency')->options(self::PROFICIENCY)->inline()->columnSpanFull(),
                TextInput::make('years')->label('Years of experience')->numeric()->minValue(0)->maxValue(60),
                TextInput::make('icon')->label('Icon key')->placeholder('laravel')->helperText('simple-icons.org slug, optional.')->maxLength(100),
                SpatieMediaLibraryFileUpload::make('icon_file')->label('Custom icon')->collection('icon')->acceptedFileTypes(['image/svg+xml', 'image/png', 'image/webp'])->maxSize(1024),
                Toggle::make('is_visible')->label('Show on the site')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('category')->withCount(['experiences', 'projects']))
            ->columns([
                TextColumn::make('name')->weight('medium')->searchable(),
                SelectColumn::make('proficiency')->options(self::PROFICIENCY)->placeholder('—'),
                TextColumn::make('years')->suffix(' yrs')->placeholder('—')->sortable(),
                TextColumn::make('usage')
                    ->label('Used in')
                    ->state(fn (Skill $record): string => collect([
                        'role' => (int) $record->getAttribute('experiences_count'),
                        'project' => (int) $record->getAttribute('projects_count'),
                    ])->filter()->map(fn (int $count, string $noun): string => $count.' '.str($noun)->plural($count))->implode(' · ') ?: '—')
                    ->color('gray'),
                Columns::visibility(),
            ])
            ->groups([
                Group::make('category.name')->label('Category')->collapsible()
                    ->getTitleFromRecordUsing(fn (Skill $record): string => $record->category->name ?? 'Stack only'),
            ])
            ->defaultGroup('category.name')
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->filters([
                SelectFilter::make('category')->relationship('category', 'name'),
                TernaryFilter::make('stack_only')
                    ->label('Stack only (no category)')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNull('skill_category_id'),
                        false: fn (Builder $query) => $query->whereNotNull('skill_category_id'),
                    ),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon(Heroicon::OutlinedCpuChip);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSkills::route('/'),
        ];
    }
}
