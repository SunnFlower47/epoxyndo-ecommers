<?php

namespace App\Filament\Resources\VisitorLogs;

use App\Filament\Resources\VisitorLogs\Pages\CreateVisitorLog;
use App\Filament\Resources\VisitorLogs\Pages\EditVisitorLog;
use App\Filament\Resources\VisitorLogs\Pages\ListVisitorLogs;
use App\Filament\Resources\VisitorLogs\Pages\ViewVisitorLog;
use App\Filament\Resources\VisitorLogs\Schemas\VisitorLogForm;
use App\Filament\Resources\VisitorLogs\Schemas\VisitorLogInfolist;
use App\Filament\Resources\VisitorLogs\Tables\VisitorLogsTable;
use App\Models\VisitorLog;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VisitorLogResource extends Resource
{
    protected static ?string $model = VisitorLog::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';

    protected static string|\UnitEnum|null $navigationGroup = 'Laporan & Log';
    
    public static function getModelLabel(): string
    {
        return 'Log Pengunjung';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Log Pengunjung';
    }

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return VisitorLogForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return VisitorLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VisitorLogsTable::configure($table);
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
            'index' => ListVisitorLogs::route('/'),
            'create' => CreateVisitorLog::route('/create'),
            'view' => ViewVisitorLog::route('/{record}'),
            'edit' => EditVisitorLog::route('/{record}/edit'),
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
