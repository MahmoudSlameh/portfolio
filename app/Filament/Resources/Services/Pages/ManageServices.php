<?php

namespace App\Filament\Resources\Services\Pages;

use App\Filament\Resources\Services\ServiceResource;
use App\Models\SiteSetting;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;

class ManageServices extends ManageRecords
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sectionHeading')
                ->label('Section heading')
                ->icon(Heroicon::OutlinedPencilSquare)
                ->color('gray')
                ->modalDescription('Leave a field empty to use the active template\'s default text.')
                ->fillForm(fn (): array => SiteSetting::current()->only(['services_kicker', 'services_title', 'services_highlight']))
                ->schema([
                    TextInput::make('services_kicker')->label('Kicker')->placeholder('Services')->maxLength(60),
                    TextInput::make('services_title')->label('Title')->placeholder('Building systems')->maxLength(120),
                    TextInput::make('services_highlight')
                        ->label('Highlighted part')
                        ->placeholder('that stay calm under real-world load')
                        ->helperText('Shown after the title in the accent color.')
                        ->maxLength(160),
                ])
                ->action(function (array $data): void {
                    SiteSetting::current()->update($data);

                    Notification::make()->success()->title('Section heading saved')->send();
                }),
            CreateAction::make(),
        ];
    }
}
