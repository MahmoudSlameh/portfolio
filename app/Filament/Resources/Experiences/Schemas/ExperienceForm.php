<?php

namespace App\Filament\Resources\Experiences\Schemas;

use App\Enums\CareerBranch;
use App\Enums\EmploymentType;
use App\Enums\WorkMode;
use App\Filament\Resources\Companies\Schemas\CompanyForm;
use App\Filament\Support\Fields;
use App\Models\Company;
use App\Models\Experience;
use App\Support\Content\ChangelogMetadata;
use App\Support\Media\InitialsAvatar;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ExperienceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 3])->schema([
                Group::make([
                    Section::make('Position')
                        ->icon(Heroicon::OutlinedBriefcase)
                        ->columns(2)
                        ->schema([
                            Select::make('company_id')
                                ->label('Company')
                                ->relationship('company', 'name')
                                ->getOptionLabelFromRecordUsing(fn (Company $company): string => self::companyLabel($company))
                                ->allowHtml()
                                ->searchable(['name'])
                                ->preload()
                                ->live()
                                ->createOptionForm(CompanyForm::quick())
                                ->editOptionForm(CompanyForm::quick())
                                ->helperText('Leave empty for open-source, freelance or personal work.'),
                            TextInput::make('organization_name')
                                ->label('Organization')
                                ->placeholder('Quire (open source)')
                                ->maxLength(255)
                                ->visible(fn (Get $get): bool => blank($get('company_id')))
                                ->required(fn (Get $get): bool => blank($get('company_id'))),
                            TextInput::make('role')
                                ->label('Job title')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('Senior Software Engineer'),
                            Select::make('employment_type')
                                ->label('Employment type')
                                ->options(EmploymentType::class)
                                ->default(EmploymentType::FullTime->value)
                                ->required(),
                            ToggleButtons::make('work_mode')
                                ->label('Work mode')
                                ->options(WorkMode::class)
                                ->default(WorkMode::OnSite->value)
                                ->inline()
                                ->required()
                                ->columnSpanFull(),
                        ]),
                    Section::make('Location')
                        ->icon(Heroicon::OutlinedMapPin)
                        ->columns(2)
                        ->schema([
                            Fields::country(),
                            TextInput::make('city')->maxLength(255),
                            TextInput::make('address')
                                ->label('Address')
                                ->placeholder('Optional')
                                ->maxLength(255)
                                ->columnSpanFull(),
                        ]),
                    Section::make('Period')
                        ->icon(Heroicon::OutlinedCalendarDays)
                        ->columns(2)
                        ->schema([
                            Fields::month('start_date')->label('Start')->required()->maxDate(now()),
                            Fields::month('end_date')
                                ->label('End')
                                ->afterOrEqual('start_date')
                                ->maxDate(now()->endOfMonth())
                                ->hidden(fn (Get $get): bool => (bool) $get('is_current'))
                                ->required(fn (Get $get): bool => ! $get('is_current')),
                            Fields::currentToggle('I currently work here')->columnSpanFull(),
                        ]),
                    Section::make('Description')
                        ->icon(Heroicon::OutlinedDocumentText)
                        ->schema([
                            Textarea::make('summary')
                                ->rows(3)
                                ->maxLength(1000)
                                ->helperText('A short description of the role.'),
                            Repeater::make('highlights')
                                ->defaultItems(0)
                                ->label('Achievements')
                                ->simple(Textarea::make('achievement')->rows(2)->required()->maxLength(500))
                                ->reorderable()
                                ->addActionLabel('Add achievement'),
                            Fields::stack(),
                        ]),
                ])->columnSpan(['lg' => 2]),
                Group::make([
                    Fields::visibilitySection(),
                    Section::make('Changelog template')
                        ->icon(Heroicon::OutlinedCodeBracketSquare)
                        ->description('Leave empty to generate them automatically.')
                        ->collapsible()
                        ->collapsed()
                        ->schema([
                            Select::make('branch')
                                ->options(CareerBranch::class)
                                ->placeholder(fn (?Experience $record): string => 'Auto: '.self::derived($record, 'branch')),
                            TextInput::make('version')->maxLength(50)->placeholder(fn (?Experience $record): string => self::derived($record, 'version')),
                            TextInput::make('commit_hash')->label('Commit')->length(7)->placeholder(fn (?Experience $record): string => self::derived($record, 'commit')),
                            TextInput::make('commit_message')->maxLength(255)->placeholder(fn (?Experience $record): string => self::derived($record, 'message')),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]),
        ]);
    }

    public static function companyLabel(Company $company): string
    {
        $logo = $company->getFirstMediaUrl('logo', 'thumb') ?: $company->getFirstMediaUrl('logo') ?: InitialsAvatar::dataUri($company->name);

        return '<span class="flex items-center gap-2"><img src="'.e($logo).'" alt="" class="size-5 rounded object-contain">'.e($company->name).'</span>';
    }

    private static function derived(?Experience $record, string $key): string
    {
        if ($record === null) {
            return 'generated on save';
        }

        $value = ChangelogMetadata::for(Experience::query()->with('company')->get())[$record->id][$key] ?? null;

        return $value instanceof CareerBranch ? $value->value : (string) $value;
    }
}
