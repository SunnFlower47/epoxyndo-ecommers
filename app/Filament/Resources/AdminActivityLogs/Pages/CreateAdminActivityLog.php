<?php

namespace App\Filament\Resources\AdminActivityLogs\Pages;

use App\Filament\Resources\AdminActivityLogs\AdminActivityLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdminActivityLog extends CreateRecord
{
    protected static string $resource = AdminActivityLogResource::class;
}
