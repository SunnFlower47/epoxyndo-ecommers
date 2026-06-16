<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \pxlrbt\FilamentExcel\Actions\Pages\ExportAction::make()
                ->exports([
                    \pxlrbt\FilamentExcel\Exports\ExcelExport::make()
                        ->fromTable()
                        ->withFilename(fn ($resource) => 'users_export_' . date('Y-m-d_H-i-s')),
                ]),
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => \Filament\Schemas\Components\Tabs\Tab::make('Semua Pengguna'),
            'customers' => \Filament\Schemas\Components\Tabs\Tab::make('Pelanggan Biasa')
                ->modifyQueryUsing(fn ($query) => $query->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['admin', 'staff']))),
            'team' => \Filament\Schemas\Components\Tabs\Tab::make('Tim Internal')
                ->modifyQueryUsing(fn ($query) => $query->whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'staff']))),
        ];
    }
}

