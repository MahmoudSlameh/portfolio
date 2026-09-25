<?php

namespace App\Filament\Resources\Certifications;

use App\Filament\Resources\Certifications\Pages\ManageCertifications;
use App\Filament\Support\Columns;
use App\Filament\Support\Fields;
use App\Models\Certification;
use App\Support\Media\MimeTypes;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class CertificationResource extends Resource
{
    protected static ?string $model = Certification::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckBadge;

    protected static string|UnitEnum|null $navigationGroup = 'Career';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')->required()->maxLength(255)->columnSpanFull(),
                TextInput::make('issuer')->required()->maxLength(255)->placeholder('Amazon Web Services'),
                TextInput::make('credential_id')->label('Credential ID')->maxLength(255),
                DatePicker::make('issued_at')->label('Issued')->native(false)->required()->maxDate(now()),
                DatePicker::make('expires_at')->label('Expires')->native(false)->afterOrEqual('issued_at'),
                TextInput::make('credential_url')->label('Verification link')->url()->prefixIcon(Heroicon::OutlinedLink)->maxLength(255)->columnSpanFull(),
                Fields::image('badge', acceptedTypes: MimeTypes::RASTER_OR_SVG)->label('Badge'),
                Toggle::make('is_visible')->label('Show on the site')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('badge')->label('')->collection('badge')->conversion('thumb')->imageSize(32),
                TextColumn::make('name')->weight('medium')->description(fn (Certification $record): string => $record->issuer)->searchable(['name', 'issuer']),
                TextColumn::make('issued_at')->label('Issued')->date('M Y')->sortable(),
                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->date('M Y')
                    ->placeholder('No expiry')
                    ->badge(fn (Certification $record): bool => $record->is_expired)
                    ->color(fn (Certification $record): ?string => $record->is_expired ? 'danger' : null)
                    ->formatStateUsing(fn (Certification $record): string => ($record->is_expired ? 'Expired · ' : '').$record->expires_at?->format('M Y')),
                Columns::visibility(),
            ])
            ->reorderable('sort_order')
            ->defaultSort('issued_at', 'desc')
            ->recordActions([
                Action::make('verify')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->color('gray')
                    ->url(fn (Certification $record): ?string => $record->credential_url, shouldOpenInNewTab: true)
                    ->visible(fn (Certification $record): bool => filled($record->credential_url)),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon(Heroicon::OutlinedCheckBadge);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCertifications::route('/'),
        ];
    }
}
