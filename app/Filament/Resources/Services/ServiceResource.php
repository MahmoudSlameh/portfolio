<?php

namespace App\Filament\Resources\Services;

use App\Enums\ServiceIcon;
use App\Filament\Resources\Services\Pages\ManageServices;
use App\Filament\Support\Columns;
use App\Models\Service;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static string|UnitEnum|null $navigationGroup = 'Work';

    protected static ?int $navigationSort = 0;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('title')->required()->placeholder('SaaS & Multi-tenant Platforms')->maxLength(255),
                Select::make('icon')->options(ServiceIcon::class)->default(ServiceIcon::Sparkles)->required()->native(false),
                Textarea::make('summary')
                    ->required()
                    ->rows(3)
                    ->maxLength(500)
                    ->placeholder('From idea to a running product with subscriptions, roles and an admin panel.')
                    ->helperText('Describe the outcome for the client, not the tools.')
                    ->columnSpanFull(),
                TagsInput::make('highlights')
                    ->placeholder('Add a highlight and press Enter')
                    ->helperText('Short deliverables or technologies, e.g. "Payment gateways", "Laravel", "Admin dashboard".')
                    ->reorderable()
                    ->columnSpanFull(),
                Toggle::make('is_visible')->label('Show on the site')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('icon')->label(''),
                TextColumn::make('title')
                    ->weight('medium')
                    ->description(fn (Service $record): string => str($record->summary)->limit(90)->toString())
                    ->wrap()
                    ->searchable(),
                TextColumn::make('highlights')->badge()->color('gray'),
                Columns::visibility(),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateDescription('Add what you offer to clients and employers; the section is hidden while this list is empty.')
            ->emptyStateIcon(Heroicon::OutlinedBriefcase);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageServices::route('/'),
        ];
    }
}
