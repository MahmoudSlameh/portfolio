<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Enums\ArchitectureNodeKind;
use App\Enums\ProjectCategory;
use App\Enums\ProjectLinkKind;
use App\Enums\ProjectStatus;
use App\Filament\Resources\Companies\Schemas\CompanyForm;
use App\Filament\Resources\Experiences\Schemas\ExperienceForm;
use App\Filament\Support\Fields;
use App\Models\Company;
use App\Models\Experience;
use App\Support\Media\MimeTypes;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 3])->schema([
                Tabs::make('Project')
                    ->persistTabInQueryString()
                    ->columnSpan(['lg' => 2])
                    ->tabs([
                        self::basicsTab(),
                        self::storyTab(),
                        self::architectureTab(),
                        self::metricsTab(),
                        self::galleryTab(),
                        self::seoTab(),
                    ]),
                Group::make([
                    Section::make('Cover')
                        ->icon(Heroicon::OutlinedPhoto)
                        ->description('16:9, shown on cards, the case study and social previews.')
                        ->schema([
                            Fields::image('cover', ['16:9'], MimeTypes::RASTER)->hiddenLabel(),
                            Fields::alt('cover_alt'),
                        ]),
                    Section::make('Publishing')
                        ->icon(Heroicon::OutlinedRocketLaunch)
                        ->schema([
                            Toggle::make('is_published')->label('Published')->default(false),
                            DateTimePicker::make('published_at')
                                ->label('Publish date')
                                ->native(false)
                                ->helperText('Leave empty to publish immediately; a future date schedules it.'),
                            Toggle::make('is_featured')->label('Featured on the home page'),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]),
        ]);
    }

    private static function basicsTab(): Tab
    {
        return Tab::make('Basics')->icon(Heroicon::OutlinedRectangleStack)->schema([
            Section::make()->columns(2)->schema([
                Fields::slugSource('title', 'Title'),
                Fields::slug('title'),
                TextInput::make('tagline')->maxLength(255)->columnSpanFull()->placeholder('A double-entry ledger that has never been out of balance.'),
                Textarea::make('summary')->rows(3)->maxLength(1000)->columnSpanFull(),
                TextInput::make('year')->numeric()->required()->minValue(1990)->maxValue((int) date('Y') + 1)->default((int) date('Y')),
                TextInput::make('version')->placeholder('v3.2.0')->maxLength(50),
                Select::make('status')->options(ProjectStatus::class)->default(ProjectStatus::Live->value)->required(),
                Select::make('category')->options(ProjectCategory::class)->default(ProjectCategory::Product->value)->required(),
            ]),
            Section::make('Context')->icon(Heroicon::OutlinedBriefcase)->columns(2)->schema([
                Select::make('company_id')
                    ->label('Company / client')
                    ->relationship('company', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Company $company): string => ExperienceForm::companyLabel($company))
                    ->allowHtml()
                    ->searchable(['name'])
                    ->preload()
                    ->live()
                    ->createOptionForm(CompanyForm::quick()),
                Select::make('experience_id')
                    ->label('Role')
                    ->relationship(
                        'experience',
                        'role',
                        fn (Builder $query, Get $get) => $query->when($get('company_id'), fn (Builder $query, mixed $companyId) => $query->where('company_id', $companyId)),
                    )
                    ->getOptionLabelFromRecordUsing(fn (Experience $experience): string => "{$experience->role} · {$experience->organization}")
                    ->searchable(['role'])
                    ->preload(),
                TextInput::make('role')->label('Your role on the project')->placeholder('Tech lead & architect')->maxLength(255),
                TextInput::make('team')->placeholder('7 engineers, 1 PM')->maxLength(255),
                TextInput::make('timeline')->placeholder('Mar 2024 — ongoing')->maxLength(255),
                Fields::stack()->columnSpanFull(),
            ]),
        ]);
    }

    private static function storyTab(): Tab
    {
        return Tab::make('Story')->icon(Heroicon::OutlinedDocumentText)->schema([
            Section::make('Overview')->schema([self::paragraphs('overview')]),
            Section::make('Problem')->schema([self::paragraphs('problem')]),
            Section::make('Approach')->schema([self::titledItems('approach', 'Add step')]),
            Section::make('Features')->schema([self::titledItems('features', 'Add feature')]),
            Section::make('Challenges')->schema([self::titledItems('challenges', 'Add challenge')]),
        ]);
    }

    private static function architectureTab(): Tab
    {
        return Tab::make('Architecture')->icon(Heroicon::OutlinedCpuChip)->schema([
            Section::make('Diagram')
                ->description('Nodes are placed on a grid (column × row); edges connect node ids. Leave empty to hide the diagram.')
                ->columns(3)
                ->schema([
                    TextInput::make('architecture.caption')->label('Caption')->columnSpanFull(),
                    TextInput::make('architecture.columns')->label('Columns')->numeric()->minValue(1)->maxValue(6)->default(3),
                    TextInput::make('architecture.rows')->label('Rows')->numeric()->minValue(1)->maxValue(6)->default(2),
                    Repeater::make('architecture.nodes')
                        ->label('Nodes')
                        ->defaultItems(0)
                        ->schema([
                            TextInput::make('id')->required()->alphaDash()->maxLength(50),
                            TextInput::make('label')->required()->maxLength(100),
                            Select::make('kind')->options(ArchitectureNodeKind::class)->required(),
                            TextInput::make('detail')->maxLength(150)->columnSpan(2),
                            TextInput::make('column')->numeric()->minValue(1)->required(),
                            TextInput::make('row')->numeric()->minValue(1)->required(),
                        ])
                        ->columns(3)
                        ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                        ->collapsible()
                        ->reorderable()
                        ->addActionLabel('Add node')
                        ->columnSpanFull(),
                    Repeater::make('architecture.edges')
                        ->label('Edges')
                        ->defaultItems(0)
                        ->schema([
                            Select::make('from')->options(fn (Get $get): array => self::nodeOptions($get))->required(),
                            Select::make('to')->options(fn (Get $get): array => self::nodeOptions($get))->required(),
                            TextInput::make('label')->maxLength(50),
                        ])
                        ->columns(3)
                        ->addActionLabel('Add edge')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    private static function metricsTab(): Tab
    {
        return Tab::make('Metrics & links')->icon(Heroicon::OutlinedChartBar)->schema([
            Section::make('Metrics')->schema([
                Repeater::make('metrics')
                    ->hiddenLabel()
                    ->defaultItems(0)
                    ->schema([
                        TextInput::make('value')->required()->placeholder('12 min'),
                        TextInput::make('label')->required()->placeholder('close time'),
                        TextInput::make('detail')->placeholder('down from 9 hours'),
                    ])
                    ->columns(3)
                    ->reorderable()
                    ->addActionLabel('Add metric'),
            ]),
            Section::make('Links')->schema([
                Repeater::make('links')
                    ->hiddenLabel()
                    ->defaultItems(0)
                    ->schema([
                        TextInput::make('label')->required(),
                        TextInput::make('url')->url()->required(),
                        Select::make('kind')->options(ProjectLinkKind::class)->required(),
                    ])
                    ->columns(3)
                    ->reorderable()
                    ->addActionLabel('Add link'),
            ]),
        ]);
    }

    private static function galleryTab(): Tab
    {
        return Tab::make('Gallery')->icon(Heroicon::OutlinedPhoto)->schema([
            Section::make()
                ->description('Images shown in the case study (4:3 works best). Each image has its own alt text and caption.')
                ->schema([
                    Repeater::make('galleryItems')
                        ->hiddenLabel()
                        ->relationship()
                        ->orderColumn('sort_order')
                        ->defaultItems(0)
                        ->schema([
                            SpatieMediaLibraryFileUpload::make('image')
                                ->collection('image')
                                ->image()
                                ->imageEditor()
                                ->imageEditorAspectRatioOptions(['4:3', '16:9'])
                                ->maxSize(10 * 1024)
                                ->required(),
                            TextInput::make('alt')->label('Alt text')->required()->maxLength(255),
                            TextInput::make('caption')->maxLength(255),
                        ])
                        ->grid(['md' => 2])
                        ->itemLabel(fn (array $state): ?string => $state['caption'] ?? null)
                        ->collapsible()
                        ->addActionLabel('Add image'),
                ]),
        ]);
    }

    private static function seoTab(): Tab
    {
        return Tab::make('SEO')->icon(Heroicon::OutlinedMagnifyingGlass)->schema([
            Section::make()
                ->description('Optional overrides. By default the title and summary are used.')
                ->schema([
                    TextInput::make('meta_title')
                        ->maxLength(70)
                        ->live(onBlur: true)
                        ->hint(fn (?string $state): string => mb_strlen((string) $state).' / 60'),
                    Textarea::make('meta_description')
                        ->rows(2)
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->hint(fn (?string $state): string => mb_strlen((string) $state).' / 160'),
                ]),
        ]);
    }

    private static function paragraphs(string $name): Repeater
    {
        return Repeater::make($name)
            ->hiddenLabel()
            ->defaultItems(0)
            ->simple(Textarea::make('paragraph')->rows(3)->required())
            ->reorderable()
            ->addActionLabel('Add paragraph');
    }

    private static function titledItems(string $name, string $addLabel): Repeater
    {
        return Repeater::make($name)
            ->hiddenLabel()
            ->defaultItems(0)
            ->schema([
                TextInput::make('title')->required()->maxLength(255),
                Textarea::make('description')->rows(2)->required(),
            ])
            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
            ->collapsible()
            ->reorderable()
            ->addActionLabel($addLabel);
    }

    /**
     * Node ids of the diagram, read from inside an edge item.
     *
     * @return array<string, string>
     */
    private static function nodeOptions(Get $get): array
    {
        return collect((array) $get('../../nodes'))
            ->filter(fn (mixed $node): bool => is_array($node) && filled($node['id'] ?? null))
            ->mapWithKeys(fn (array $node): array => [(string) $node['id'] => (string) ($node['label'] ?? $node['id'])])
            ->all();
    }
}
