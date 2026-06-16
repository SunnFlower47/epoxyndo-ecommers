<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\VisitorLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $todayRevenue = Order::query()
            ->whereDate('created_at', today())
            ->where('payment_status', 'paid')
            ->sum('grand_total');

        $todayOrders = Order::query()
            ->whereDate('created_at', today())
            ->count();

        $activeProducts = Product::query()
            ->where('is_active', true)
            ->count();

        $totalUsers = User::query()->count();

        return [
            Stat::make('Revenue Hari Ini', 'Rp '.number_format($todayRevenue, 0, ',', '.'))
                ->description('Total pembayaran yang masuk hari ini')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Pesanan Hari Ini', $todayOrders)
                ->description('Pesanan baru masuk hari ini')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),

            Stat::make('Produk Aktif', $activeProducts)
                ->description('Produk yang sedang ditampilkan')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info'),

            Stat::make('Total Pengguna', $totalUsers)
                ->description('Pelanggan terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),
        ];
    }
}
