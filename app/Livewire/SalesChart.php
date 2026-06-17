<?php

namespace App\Livewire;

use Filament\Widgets\ChartWidget;

class SalesChart extends ChartWidget
{
    protected ?string $heading = 'Grafik Pendapatan (30 Hari Terakhir)';

    protected function getData(): array
    {
        $data = [];
        $labels = [];
        
        $startDate = now()->subDays(30)->startOfDay();
        $endDate = now()->endOfDay();
        
        $orders = \App\Models\Order::where('payment_status', \App\Models\Order::PAYMENT_PAID)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->groupBy(function($date) {
                return \Carbon\Carbon::parse($date->created_at)->format('d M');
            });
            
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('d M');
            $labels[] = $date;
            
            if (isset($orders[$date])) {
                $data[] = $orders[$date]->sum('grand_total');
            } else {
                $data[] = 0;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pendapatan',
                    'data' => $data,
                    'fill' => 'start',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
