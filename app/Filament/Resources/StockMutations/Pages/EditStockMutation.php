<?php

namespace App\Filament\Resources\StockMutations\Pages;

use App\Filament\Resources\StockMutations\StockMutationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditStockMutation extends EditRecord
{
    protected static string $resource = StockMutationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
