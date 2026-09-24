<?php

namespace App\Filament\Support;

use App\Models\Skill;
use App\Support\Countries;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
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

    /**
     * Spatie Media Library image upload with the image editor (optionally locked to aspect ratios like "16:9").
     *
     * @param  list<string>  $aspectRatios
     * @param  list<string>|null  $acceptedTypes
     */
    public static function image(string $collection, array $aspectRatios = [], ?array $acceptedTypes = null): SpatieMediaLibraryFileUpload
    {
        $upload = SpatieMediaLibraryFileUpload::make($collection)
            ->collection($collection)
            ->image()
            ->maxSize(10 * 1024)
            ->downloadable()
            ->openable();

        if ($acceptedTypes !== null) {
            $upload->acceptedFileTypes($acceptedTypes);
        }

        if ($aspectRatios !== []) {
            $upload->imageEditor()->imageEditorAspectRatioOptions($aspectRatios);
        }

        return $upload;
    }

    /**
     * Required alt text for an image (accessibility + SEO).
     */
    public static function alt(string $name, string $placeholder = 'Describe the image for screen readers and search engines'): TextInput
    {
        return TextInput::make($name)
            ->label('Alt text')
            ->placeholder($placeholder)
            ->maxLength(255);
    }

    /**
     * Ordered tech stack (skills). Saved by the page with {@see SyncsStack} so the chosen order is kept.
     */
    public static function stack(): Select
    {
        return Select::make('stack')
            ->label('Tech stack')
            ->multiple()
            ->searchable()
            ->preload()
            ->options(fn (): array => Skill::query()->orderBy('name')->pluck('name', 'id')->all())
            ->afterStateHydrated(function (Select $component, ?Model $record): void {
                if ($record !== null && method_exists($record, 'skills')) {
                    $component->state($record->skills()->pluck('skills.id')->map(fn (mixed $id): string => (string) $id)->all());
                }
            })
            ->dehydrated(false)
            ->createOptionForm([
                TextInput::make('name')->required()->unique(Skill::class, 'name')->maxLength(255),
            ])
            ->createOptionUsing(fn (array $data): int => Skill::query()->create(['name' => $data['name']])->id)
            ->helperText('Order matters: the first technologies are shown first.');
    }
}
