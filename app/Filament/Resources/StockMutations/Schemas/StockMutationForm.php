<?php

namespace App\Filament\Resources\StockMutations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StockMutationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required(),
                Select::make('type')
                    ->options(['in' => 'In', 'out' => 'Out'])
                    ->required(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric(),
                TextInput::make('reference_type'),
                TextInput::make('reference_id')
                    ->numeric(),
                TextInput::make('notes'),
                TextInput::make('created_by')
                    ->numeric(),
            ]);
    }
}
