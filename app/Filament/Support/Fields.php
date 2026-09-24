<?php

namespace App\Filament\Support;

use App\Support\Countries;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

/**
 * Reusable form fields shared by the panel resources.
 */
final class Fields
{
    /**
     * Searchable ISO-3166 country select with flags; stores the alpha-2 code.
     */
    public static function country(string $name = 'country_code'): Select
    {
        return Select::make($name)
            ->label('Country')
            ->options(fn (): array => collect(Countries::options())
                ->map(fn (string $country, string $code): string => Countries::flag($code).'  '.$country)
                ->all())
            ->searchable()
            ->native(false);
    }

    /**
     * Month-precision date (stored as the first day of the month).
     */
    public static function month(string $name): DatePicker
    {
        return DatePicker::make($name)
            ->native(false)
            ->displayFormat('M Y')
            ->closeOnDateSelection()
            ->prefixIcon(Heroicon::OutlinedCalendarDays)
            ->dehydrateStateUsing(fn (mixed $state): ?string => filled($state)
                ? CarbonImmutable::parse((string) $state)->startOfMonth()->toDateString()
                : null);
    }

    /**
     * "Still ongoing" toggle that hides the end date and clears it (end date null = current).
     */
    public static function currentToggle(string $label, string $endField = 'end_date'): Toggle
    {
        return Toggle::make('is_current')
            ->label($label)
            ->live()
            ->dehydrated(false)
            ->afterStateHydrated(fn (Toggle $component, Get $get) => $component->state(blank($get($endField))))
            ->afterStateUpdated(function (bool $state, Set $set) use ($endField): void {
                if ($state) {
                    $set($endField, null);
                }
            });
    }

    /**
     * Slug input that follows the source field until edited manually.
     */
    public static function slug(string $source = 'name'): TextInput
    {
        return TextInput::make('slug')
            ->helperText('Used in URLs. Generated from the '.Str::of($source)->replace('_', ' ').' when left empty.')
            ->maxLength(255)
            ->alphaDash()
            ->unique(ignoreRecord: true);
    }

    /**
     * Source field (name / title) that fills an empty slug as you type.
     */
    public static function slugSource(string $name, string $label): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->required()
            ->maxLength(255)
            ->live(onBlur: true)
            ->afterStateUpdated(function (?string $state, Get $get, Set $set, string $operation): void {
                if ($operation === 'create' || blank($get('slug'))) {
                    $set('slug', Str::slug((string) $state));
                }
            });
    }

    /**
     * Aside section with the visibility toggle.
     */
    public static function visibilitySection(string $description = 'Hidden items stay in the panel but are not shown on the site.'): Section
    {
        return Section::make('Visibility')
            ->icon(Heroicon::OutlinedEye)
            ->description($description)
            ->schema([
                Toggle::make('is_visible')
                    ->label('Show on the site')
                    ->default(true),
            ]);
    }
}
