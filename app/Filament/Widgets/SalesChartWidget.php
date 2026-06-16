<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class SalesChartWidget extends ChartWidget
{
    protected ?string $heading = 'Penjualan 30 Hari Terakhir';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = collect(range(29, 0))->map(function (int $daysAgo) {
            $date = now()->subDays($daysAgo);

            return [
                'date'  => $date->format('d M'),
                'total' => Order::query()
                    ->whereDate('created_at', $date)
                    ->where('payment_status', 'paid')
                    ->sum('grand_total'),
            ];
        });

        return [
            'datasets' => [
                [
                    'label'           => 'Revenue (Rp)',
                    'data'            => $data->pluck('total')->toArray(),
                    'borderColor'     => '#008943',
                    'backgroundColor' => 'rgba(0, 137, 67, 0.08)',
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
            ],
            'labels' => $data->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
