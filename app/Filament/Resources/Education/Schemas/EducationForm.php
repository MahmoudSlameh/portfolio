<?php

namespace App\Filament\Resources\Education\Schemas;

use App\Filament\Support\Fields;
use App\Support\Media\MimeTypes;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class EducationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 3])->columnSpanFull()->schema([
                Group::make([
                    Section::make('Qualification')
                        ->icon(Heroicon::OutlinedAcademicCap)
                        ->columns(2)
                        ->schema([
                            TextInput::make('degree')
                                ->label('Qualification')
                                ->placeholder('Diploma in Software Engineering')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),
                            TextInput::make('field_of_study')
                                ->label('Specialization')
                                ->placeholder('Software Engineering')
                                ->maxLength(255),
                            TextInput::make('grade')
                                ->placeholder('Distinction · 3.7 / 4.0 · Very good')
                                ->maxLength(255),
                        ]),
                    Section::make('Institution')
                        ->icon(Heroicon::OutlinedBuildingLibrary)
                        ->columns(2)
                        ->schema([
                            TextInput::make('institution')
                                ->label('University / institute')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('institution_url')
                                ->label('Website')
                                ->url()
                                ->prefixIcon(Heroicon::OutlinedLink)
                                ->maxLength(255),
                            Fields::country(),
                            TextInput::make('city')->maxLength(255),
                        ]),
                    Section::make('Period')
                        ->icon(Heroicon::OutlinedCalendarDays)
                        ->columns(2)
                        ->schema([
                            Fields::month('start_date')->label('Start')->required(),
                            Fields::month('end_date')
                                ->label('End')
                                ->afterOrEqual('start_date')
                                ->hidden(fn (Get $get): bool => (bool) $get('is_current'))
                                ->required(fn (Get $get): bool => ! $get('is_current')),
                            Fields::currentToggle("I'm currently studying here")->columnSpanFull(),
                        ]),
                    Section::make('Details')
                        ->icon(Heroicon::OutlinedDocumentText)
                        ->schema([
                            Textarea::make('description')->rows(3)->maxLength(1000),
                            Repeater::make('achievements')
                                ->defaultItems(0)
                                ->simple(Textarea::make('achievement')->rows(2)->required()->maxLength(500))
                                ->reorderable()
                                ->addActionLabel('Add achievement'),
                        ]),
                ])->columnSpan(['lg' => 2]),
                Group::make([
                    Section::make('Logo')
                        ->icon(Heroicon::OutlinedPhoto)
                        ->schema([
                            Fields::image('logo', acceptedTypes: MimeTypes::RASTER_OR_SVG)->hiddenLabel(),
                        ]),
                    Section::make('Certificate')
                        ->icon(Heroicon::OutlinedDocumentCheck)
                        ->description('Optional scan or PDF.')
                        ->schema([
                            SpatieMediaLibraryFileUpload::make('certificate')
                                ->collection('certificate')
                                ->hiddenLabel()
                                ->acceptedFileTypes(MimeTypes::RASTER_OR_PDF)
                                ->maxSize(10 * 1024)
                                ->downloadable()
                                ->openable(),
                        ]),
                    Fields::visibilitySection(),
                ])->columnSpan(['lg' => 1]),
            ]),
        ]);
    }
}
