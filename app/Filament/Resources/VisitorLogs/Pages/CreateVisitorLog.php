<?php

namespace App\Filament\Resources\VisitorLogs\Pages;

use App\Filament\Resources\VisitorLogs\VisitorLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVisitorLog extends CreateRecord
{
    protected static string $resource = VisitorLogResource::class;
}
