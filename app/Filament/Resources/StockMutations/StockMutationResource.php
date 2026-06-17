<?php

namespace App\Filament\Resources\StockMutations;

use App\Filament\Resources\StockMutations\Pages\CreateStockMutation;
use App\Filament\Resources\StockMutations\Pages\EditStockMutation;
use App\Filament\Resources\StockMutations\Pages\ListStockMutations;
use App\Filament\Resources\StockMutations\Pages\ViewStockMutation;
use App\Filament\Resources\StockMutations\Schemas\StockMutationForm;
use App\Filament\Resources\StockMutations\Schemas\StockMutationInfolist;
use App\Filament\Resources\StockMutations\Tables\StockMutationsTable;
use App\Models\StockMutation;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StockMutationResource extends Resource
{
    protected static ?string $model = StockMutation::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|\UnitEnum|null $navigationGroup = 'Laporan & Log';
    
    public static function getModelLabel(): string
    {
        return 'Mutasi Stok';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Riwayat Mutasi Stok';
    }

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return StockMutationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StockMutationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockMutationsTable::configure($table);
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
            'index' => ListStockMutations::route('/'),
            'create' => CreateStockMutation::route('/create'),
            'view' => ViewStockMutation::route('/{record}'),
            'edit' => EditStockMutation::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}
