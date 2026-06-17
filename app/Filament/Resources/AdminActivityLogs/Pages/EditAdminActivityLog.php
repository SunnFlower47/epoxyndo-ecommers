<?php

namespace App\Filament\Resources\AdminActivityLogs\Pages;

use App\Filament\Resources\AdminActivityLogs\AdminActivityLogResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAdminActivityLog extends EditRecord
{
    protected static string $resource = AdminActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
