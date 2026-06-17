<?php

namespace App\Filament\Resources\VisitorLogs\Pages;

use App\Filament\Resources\VisitorLogs\VisitorLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVisitorLogs extends ListRecords
{
    protected static string $resource = VisitorLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
