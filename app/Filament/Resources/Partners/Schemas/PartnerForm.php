<?php

namespace App\Filament\Resources\Partners\Schemas;

use Filament\Schemas\Schema;

class PartnerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('name')
                    ->label('Nama Klien / Partner')
                    ->required()
                    ->maxLength(255),
                \Filament\Forms\Components\FileUpload::make('logo')
                    ->label('Logo')
                    ->image()
                    ->directory('settings/clients')
                    ->required(),
                \Filament\Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
                \Filament\Forms\Components\TextInput::make('sort_order')
                    ->label('Urutan Tampil')
                    ->numeric()
                    ->default(0),
            ]);
    }
}
