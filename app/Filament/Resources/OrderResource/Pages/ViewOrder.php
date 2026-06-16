<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('request_pickup')
                ->label('Request Pickup (Biteship)')
                ->icon('heroicon-o-truck')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Request Pickup Kurir')
                ->modalDescription('Apakah Anda yakin ingin memanggil API kurir untuk pesanan ini? Pastikan detail alamat tujuan dan kurir sudah terisi.')
                ->action(function (\App\Models\Order $record) {
                    try {
                        $service = new \App\Services\BiteshipService();
                        $service->createOrder($record);

                        // Update status order
                        $record->update(['status' => \App\Models\Order::STATUS_PROCESSING]);

                        \Filament\Notifications\Notification::make()
                            ->title('Pickup Berhasil Diajukan')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Gagal Request Pickup')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn (\App\Models\Order $record) => !$record->shipment && $record->courier),
            
            \Filament\Actions\EditAction::make(),
        ];
    }
}
