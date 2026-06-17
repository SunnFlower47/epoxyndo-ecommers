<?php

namespace App\Filament\Resources\VisitorLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class VisitorLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('ip_address')
                    ->placeholder('-'),
                TextEntry::make('user_agent')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('visited_url')
                    ->placeholder('-'),
                TextEntry::make('user.name')
                    ->label('User')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
