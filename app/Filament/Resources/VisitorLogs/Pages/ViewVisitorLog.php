<?php

namespace App\Filament\Resources\VisitorLogs\Pages;

use App\Filament\Resources\VisitorLogs\VisitorLogResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewVisitorLog extends ViewRecord
{
    protected static string $resource = VisitorLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
