<?php

namespace App\Filament\Support;

use Carbon\CarbonImmutable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Model;

/**
 * Reusable table columns.
 */
final class Columns
{
    /**
     * "Feb 2024 — Present" with the duration as description ("1 yr 8 mos").
     */
    public static function period(string $start = 'start_date', string $end = 'end_date'): TextColumn
    {
        return TextColumn::make($start)
            ->label('Period')
            ->sortable()
            ->formatStateUsing(function (Model $record) use ($start, $end): string {
                /** @var CarbonImmutable $from */
                $from = $record->getAttribute($start);
                /** @var CarbonImmutable|null $to */
                $to = $record->getAttribute($end);

                return $from->format('M Y').' — '.($to?->format('M Y') ?? 'Present');
            })
            ->description(fn (Model $record): string => self::duration($record->getAttribute($start), $record->getAttribute($end)))
            ->badge(fn (Model $record): bool => $record->getAttribute($end) === null)
            ->color(fn (Model $record): ?string => $record->getAttribute($end) === null ? 'success' : null);
    }

    public static function visibility(): ToggleColumn
    {
        return ToggleColumn::make('is_visible')
            ->label('Visible')
            ->alignCenter();
    }

    public static function duration(?CarbonImmutable $from, ?CarbonImmutable $to): string
    {
        if ($from === null) {
            return '';
        }

        $months = (int) $from->startOfMonth()->diffInMonths(($to ?? CarbonImmutable::now())->startOfMonth()) + 1;
        $years = intdiv($months, 12);
        $remainder = $months % 12;

        return collect([
            $years > 0 ? $years.' '.($years === 1 ? 'yr' : 'yrs') : null,
            $remainder > 0 ? $remainder.' '.($remainder === 1 ? 'mo' : 'mos') : null,
        ])->filter()->implode(' ');
    }
}
