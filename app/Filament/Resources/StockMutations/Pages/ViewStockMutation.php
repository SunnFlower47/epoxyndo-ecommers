<?php

namespace App\Filament\Resources\StockMutations\Pages;

use App\Filament\Resources\StockMutations\StockMutationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStockMutation extends ViewRecord
{
    protected static string $resource = StockMutationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
