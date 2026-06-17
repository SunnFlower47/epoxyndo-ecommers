<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class SalesReport extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    
    protected static string|\UnitEnum|null $navigationGroup = 'Laporan & Log';
    
    protected static ?string $navigationLabel = 'Laporan Penjualan';
    
    protected static ?string $title = 'Laporan Penjualan';

    protected string $view = 'filament.pages.sales-report';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Livewire\SalesOverview::class,
            \App\Livewire\SalesChart::class,
        ];
    }
}
