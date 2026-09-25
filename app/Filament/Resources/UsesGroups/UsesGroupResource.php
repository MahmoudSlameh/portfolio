<?php

namespace App\Filament\Resources\UsesGroups;

use App\Filament\Resources\UsesGroups\Pages\CreateUsesGroup;
use App\Filament\Resources\UsesGroups\Pages\EditUsesGroup;
use App\Filament\Resources\UsesGroups\Pages\ListUsesGroups;
use App\Filament\Resources\UsesGroups\Schemas\UsesGroupForm;
use App\Filament\Resources\UsesGroups\Tables\UsesGroupsTable;
use App\Models\UsesGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class UsesGroupResource extends Resource
{
    protected static ?string $model = UsesGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedComputerDesktop;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Uses';

    protected static ?string $modelLabel = 'uses group';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return UsesGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsesGroupsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsesGroups::route('/'),
            'create' => CreateUsesGroup::route('/create'),
            'edit' => EditUsesGroup::route('/{record}/edit'),
        ];
    }
}
