<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestMessages extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = ['md' => 2, 'xl' => 1];

    public function table(Table $table): Table
    {
        return $table
            ->heading('Latest messages')
            ->query(fn (): Builder => ContactMessage::query()->latest()->limit(5))
            ->paginated(false)
            ->columns([
                TextColumn::make('name')
                    ->weight(fn (ContactMessage $record): ?string => $record->read_at ? null : 'bold')
                    ->description(fn (ContactMessage $record): string => str($record->message)->limit(60)->toString()),
                TextColumn::make('topic')->badge(),
                TextColumn::make('created_at')->label('Received')->since(),
            ])
            ->recordUrl(fn (ContactMessage $record): string => ContactMessageResource::getUrl('view', ['record' => $record]))
            ->headerActions([
                Action::make('inbox')->label('Open inbox')->icon(Heroicon::OutlinedInbox)->color('gray')->url(ContactMessageResource::getUrl()),
            ])
            ->emptyStateHeading('No messages yet')
            ->emptyStateIcon(Heroicon::OutlinedInbox);
    }
}
