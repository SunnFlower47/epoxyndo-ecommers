<?php

namespace App\Filament\Resources\VisitorLogs\Pages;

use App\Filament\Resources\VisitorLogs\VisitorLogResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditVisitorLog extends EditRecord
{
    protected static string $resource = VisitorLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
