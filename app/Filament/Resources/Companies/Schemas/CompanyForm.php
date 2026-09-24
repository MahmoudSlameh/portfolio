<?php

namespace App\Filament\Resources\Companies\Schemas;

use App\Enums\CompanyKind;
use App\Enums\WordmarkStyle;
use App\Filament\Support\Fields;
use App\Support\Media\MimeTypes;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['default' => 1, 'lg' => 3])->schema([
                Group::make([
                    Section::make('Brand')
                        ->icon(Heroicon::OutlinedBuildingOffice2)
                        ->description('How the company appears on the clients wall and next to your roles.')
                        ->columns(2)
                        ->schema([
                            Fields::slugSource('name', 'Name'),
                            Fields::slug('name'),
                            self::kind(),
                            self::website(),
                            TextInput::make('industry')->placeholder('Fintech')->maxLength(255),
                        ]),
                    Section::make('Engagement')
                        ->icon(Heroicon::OutlinedBriefcase)
                        ->columns(2)
                        ->schema([
                            Textarea::make('engagement')
                                ->rows(3)
                                ->columnSpanFull()
                                ->helperText('One line about what you did for them.'),
                            TextInput::make('period_label')
                                ->label('Period')
                                ->placeholder('Derived from your roles, e.g. 2021 — 2024')
                                ->maxLength(255),
                            TextInput::make('city')->maxLength(255),
                            Fields::country(),
                        ]),
                ])->columnSpan(['lg' => 2]),
                Group::make([
                    Section::make('Logo')
                        ->icon(Heroicon::OutlinedPhoto)
                        ->description('SVG, PNG or WebP. Without a logo the name is set as a wordmark.')
                        ->schema([
                            Fields::image('logo', acceptedTypes: MimeTypes::LOGO)->label('Logo'),
                            Fields::image('logo_dark', acceptedTypes: MimeTypes::LOGO)->label('Logo for dark backgrounds')->helperText('Optional.'),
                            Select::make('wordmark_style')
                                ->label('Wordmark style')
                                ->options(WordmarkStyle::class)
                                ->default(WordmarkStyle::SansBold->value)
                                ->required(),
                        ]),
                    Section::make('Visibility')
                        ->icon(Heroicon::OutlinedEye)
                        ->schema([
                            Toggle::make('is_featured')->label('Show on the clients wall')->default(true),
                            Toggle::make('is_visible')->label('Show on the site')->default(true),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]),
        ]);
    }

    /**
     * Compact form used to create or edit a company from other resources (experiences, projects, testimonials).
     *
     * @return array<Component|Field>
     */
    public static function quick(): array
    {
        return [
            TextInput::make('name')->required()->maxLength(255),
            self::kind(),
            self::website(),
            Fields::image('logo', acceptedTypes: MimeTypes::LOGO)->label('Logo'),
        ];
    }

    private static function kind(): ToggleButtons
    {
        return ToggleButtons::make('kind')
            ->options(CompanyKind::class)
            ->default(CompanyKind::Employer->value)
            ->inline()
            ->required();
    }

    private static function website(): TextInput
    {
        return TextInput::make('website_url')
            ->label('Website')
            ->url()
            ->prefixIcon(Heroicon::OutlinedLink)
            ->placeholder('https://')
            ->maxLength(255);
    }
}
