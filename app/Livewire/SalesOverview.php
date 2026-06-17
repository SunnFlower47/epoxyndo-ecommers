<?php

namespace App\Livewire;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $paidOrders = \App\Models\Order::where('payment_status', \App\Models\Order::PAYMENT_PAID);
        
        $totalRevenue = $paidOrders->sum('grand_total');
        $totalOrders = $paidOrders->count();
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        return [
            Stat::make('Total Pendapatan', 'Rp ' . number_format($totalRevenue, 0, ',', '.'))
                ->description('Total dari pesanan lunas')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            
            Stat::make('Pesanan Sukses', number_format($totalOrders, 0, ',', '.'))
                ->description('Jumlah pesanan lunas')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('primary'),
                
            Stat::make('Rata-rata Nilai Pesanan', 'Rp ' . number_format($averageOrderValue, 0, ',', '.'))
                ->description('Rata-rata per transaksi')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('info'),
        ];
    }
}
