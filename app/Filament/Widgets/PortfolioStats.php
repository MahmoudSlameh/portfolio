<?php

namespace App\Filament\Widgets;

use App\Enums\ReadingStatus;
use App\Models\Article;
use App\Models\Book;
use App\Models\ContactMessage;
use App\Models\Project;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PortfolioStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $unread = ContactMessage::query()->unread()->count();

        return [
            Stat::make('Projects', Project::query()->published()->count())
                ->description(Project::query()->count().' total · '.Project::query()->featured()->count().' featured')
                ->descriptionIcon(Heroicon::OutlinedRectangleStack)
                ->color('primary'),
            Stat::make('Articles', Article::query()->published()->count())
                ->description(Article::query()->where('status', 'draft')->count().' drafts')
                ->descriptionIcon(Heroicon::OutlinedNewspaper)
                ->color('info'),
            Stat::make('Unread messages', $unread)
                ->description(ContactMessage::query()->where('created_at', '>=', now()->subDays(30))->count().' in the last 30 days')
                ->descriptionIcon(Heroicon::OutlinedEnvelope)
                ->color($unread > 0 ? 'warning' : 'gray')
                ->chart($this->messagesPerWeek()),
            Stat::make('Books read this year', Book::query()->where('status', ReadingStatus::Read)->whereYear('finished_at', now()->year)->count())
                ->description(Book::query()->where('status', ReadingStatus::Reading)->count().' in progress')
                ->descriptionIcon(Heroicon::OutlinedBookOpen)
                ->color('success'),
        ];
    }

    /**
     * Messages received per week over the last 8 weeks (oldest first).
     *
     * @return list<float>
     */
    private function messagesPerWeek(): array
    {
        $dates = ContactMessage::query()->where('created_at', '>=', now()->subWeeks(8))->pluck('created_at');

        return array_map(
            fn (int $weeksAgo): float => (float) $dates->filter(fn ($date): bool => $date !== null
                && $date->between(now()->subWeeks($weeksAgo + 1), now()->subWeeks($weeksAgo)))->count(),
            range(7, 0),
        );
    }
}
