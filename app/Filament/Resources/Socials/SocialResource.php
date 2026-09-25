<?php

namespace App\Filament\Resources\Socials;

use App\Enums\SocialPlatform;
use App\Filament\Resources\Socials\Pages\ManageSocials;
use App\Filament\Support\Columns;
use App\Models\Social;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class SocialResource extends Resource
{
    protected static ?string $model = Social::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShare;

    protected static string|UnitEnum|null $navigationGroup = 'Profile';

    protected static ?string $navigationLabel = 'Social links';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'label';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('platform')
                    ->options(SocialPlatform::class)
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (mixed $state, Get $get, Set $set): void {
                        $platform = $state instanceof SocialPlatform ? $state : SocialPlatform::tryFrom((string) $state);

                        if ($platform !== null && blank($get('label'))) {
                            $set('label', $platform->getLabel());
                        }
                    }),
                TextInput::make('label')->required()->maxLength(255),
                TextInput::make('handle')->placeholder('@mahmoud')->maxLength(255),
                TextInput::make('url')->label('Link')->required()->maxLength(255)->rule('url:http,https,mailto'),
                Toggle::make('is_visible')->label('Show on the site')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('platform')->badge()->color('gray'),
                TextColumn::make('label')->weight('medium')->description(fn (Social $record): ?string => $record->handle),
                TextColumn::make('url')->limit(50)->color('primary')->url(fn (Social $record): string => $record->url, shouldOpenInNewTab: true),
                Columns::visibility(),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateIcon(Heroicon::OutlinedShare);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSocials::route('/'),
        ];
    }
}
