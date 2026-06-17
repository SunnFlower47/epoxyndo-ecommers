<?php

namespace App\Filament\Resources\AdminActivityLogs;

use App\Filament\Resources\AdminActivityLogs\Pages\CreateAdminActivityLog;
use App\Filament\Resources\AdminActivityLogs\Pages\EditAdminActivityLog;
use App\Filament\Resources\AdminActivityLogs\Pages\ListAdminActivityLogs;
use App\Filament\Resources\AdminActivityLogs\Pages\ViewAdminActivityLog;
use App\Filament\Resources\AdminActivityLogs\Schemas\AdminActivityLogForm;
use App\Filament\Resources\AdminActivityLogs\Schemas\AdminActivityLogInfolist;
use App\Filament\Resources\AdminActivityLogs\Tables\AdminActivityLogsTable;
use App\Models\AdminActivityLog;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AdminActivityLogResource extends Resource
{
    protected static ?string $model = AdminActivityLog::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Laporan & Log';
    
    public static function getModelLabel(): string
    {
        return 'Aktivitas Admin';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Aktivitas Admin';
    }

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return AdminActivityLogForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AdminActivityLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AdminActivityLogsTable::configure($table);
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
            'index' => ListAdminActivityLogs::route('/'),
            'create' => CreateAdminActivityLog::route('/create'),
            'view' => ViewAdminActivityLog::route('/{record}'),
            'edit' => EditAdminActivityLog::route('/{record}/edit'),
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
