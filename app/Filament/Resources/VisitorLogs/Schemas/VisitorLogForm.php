<?php

namespace App\Filament\Resources\VisitorLogs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class VisitorLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('ip_address'),
                Textarea::make('user_agent')
                    ->columnSpanFull(),
                TextInput::make('visited_url')
                    ->url(),
                Select::make('user_id')
                    ->relationship('user', 'name'),
            ]);
    }
}
