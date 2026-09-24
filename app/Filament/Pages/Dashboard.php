<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use App\Filament\Resources\Projects\ProjectResource;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;

class Dashboard extends BaseDashboard
{
    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewSite')->label('View site')->icon(Heroicon::OutlinedGlobeAlt)->color('gray')->url(url('/'), shouldOpenInNewTab: true),
            Action::make('editProfile')->label('Edit profile')->icon(Heroicon::OutlinedIdentification)->color('gray')->url(EditProfile::getUrl()),
            Action::make('newArticle')->label('New article')->icon(Heroicon::OutlinedPencilSquare)->color('gray')->url(ArticleResource::getUrl('create')),
            Action::make('newProject')->label('New project')->icon(Heroicon::OutlinedPlus)->url(ProjectResource::getUrl('create')),
        ];
    }

    public function getColumns(): int|array
    {
        return ['md' => 2, 'xl' => 2];
    }
}
