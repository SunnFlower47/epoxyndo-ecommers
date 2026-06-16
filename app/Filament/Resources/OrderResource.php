<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static string|\UnitEnum|null $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return 'Pesanan';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Pesanan';
    }

    // ─── Form ─────────────────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Status Pesanan')
                ->schema([
                    Select::make('status')
                        ->label('Status Pesanan')
                        ->options([
                            Order::STATUS_PENDING    => 'Menunggu',
                            Order::STATUS_PROCESSING => 'Diproses',
                            Order::STATUS_SHIPPED    => 'Dikirim',
                            Order::STATUS_COMPLETED  => 'Selesai',
                            Order::STATUS_CANCELLED  => 'Dibatalkan',
                        ])
                        ->required(),

                    Select::make('payment_status')
                        ->label('Status Pembayaran')
                        ->options([
                            Order::PAYMENT_UNPAID => 'Belum Bayar',
                            Order::PAYMENT_PAID   => 'Lunas',
                            Order::PAYMENT_FAILED => 'Gagal',
                        ])
                        ->required(),

                    Textarea::make('notes')
                        ->label('Catatan Internal')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    // ─── Infolist ─────────────────────────────────────────────────────────────

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Detail Pesanan')
                ->schema([
                    TextEntry::make('order_number')
                        ->label('No. Pesanan')
                        ->weight('bold')
                        ->copyable(),

                    TextEntry::make('user.name')
                        ->label('Pelanggan'),

                    TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->color(fn (string $state) => match ($state) {
                            'pending'    => 'warning',
                            'processing' => 'info',
                            'shipped'    => 'primary',
                            'completed'  => 'success',
                            'cancelled'  => 'danger',
                            default      => 'gray',
                        }),

                    TextEntry::make('payment_status')
                        ->label('Pembayaran')
                        ->badge()
                        ->color(fn (string $state) => match ($state) {
                            'paid'   => 'success',
                            'unpaid' => 'warning',
                            'failed' => 'danger',
                            default  => 'gray',
                        }),

                    TextEntry::make('grand_total')
                        ->label('Total')
                        ->money('IDR')
                        ->weight('bold'),

                    TextEntry::make('created_at')
                        ->label('Tanggal Pesan')
                        ->dateTime('d M Y H:i'),
                ])
                ->columns(3),

            Section::make('Informasi Pengiriman')
                ->schema([
                    TextEntry::make('shipping_address.name')
                        ->label('Penerima')
                        ->state(fn (Order $record) => $record->shipping_address['name'] ?? '-'),
                    
                    TextEntry::make('shipping_address.phone')
                        ->label('No. HP')
                        ->state(fn (Order $record) => $record->shipping_address['phone'] ?? '-'),
                    
                    TextEntry::make('shipping_address.full_address')
                        ->label('Alamat Lengkap')
                        ->state(fn (Order $record) => $record->shipping_address['full_address'] ?? '-')
                        ->columnSpanFull(),
                    
                    TextEntry::make('courier')
                        ->label('Kurir')
                        ->state(fn (Order $record) => strtoupper($record->courier ?? '-')),
                    
                    TextEntry::make('courier_service')
                        ->label('Layanan')
                        ->state(fn (Order $record) => strtoupper($record->courier_service ?? '-')),
                ])
                ->columns(2),

            Section::make('Status Resi & Pengiriman (Biteship)')
                ->schema([
                    TextEntry::make('shipment.tracking_number')
                        ->label('No. Resi')
                        ->copyable()
                        ->weight('bold')
                        ->state(fn (Order $record) => $record->shipment?->tracking_number ?? '-'),
                        
                    TextEntry::make('shipment.status')
                        ->label('Status Pengiriman')
                        ->badge()
                        ->state(fn (Order $record) => $record->shipment?->status ?? '-')
                        ->color(fn (string $state) => match ($state) {
                            'allocated', 'picking_up' => 'warning',
                            'picked' => 'info',
                            'dropping_off' => 'primary',
                            'delivered' => 'success',
                            'rejected', 'cancelled' => 'danger',
                            default => 'gray',
                        }),
                ])
                ->columns(2)
                ->visible(fn (Order $record) => $record->shipment !== null),
        ]);
    }

    // ─── Table ────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('No. Pesanan')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('user.name')
                    ->label('Pelanggan')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending'    => 'warning',
                        'processing' => 'info',
                        'shipped'    => 'primary',
                        'completed'  => 'success',
                        'cancelled'  => 'danger',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending'    => 'Menunggu',
                        'processing' => 'Diproses',
                        'shipped'    => 'Dikirim',
                        'completed'  => 'Selesai',
                        'cancelled'  => 'Dibatalkan',
                        default      => $state,
                    }),

                TextColumn::make('payment_status')
                    ->label('Pembayaran')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'paid'   => 'success',
                        'unpaid' => 'warning',
                        'failed' => 'danger',
                        default  => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'paid'   => 'Lunas',
                        'unpaid' => 'Belum Bayar',
                        'failed' => 'Gagal',
                        default  => $state,
                    }),

                TextColumn::make('grand_total')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status Pesanan')
                    ->options([
                        'pending'    => 'Menunggu',
                        'processing' => 'Diproses',
                        'shipped'    => 'Dikirim',
                        'completed'  => 'Selesai',
                        'cancelled'  => 'Dibatalkan',
                    ]),
                SelectFilter::make('payment_status')
                    ->label('Status Pembayaran')
                    ->options([
                        'paid'   => 'Lunas',
                        'unpaid' => 'Belum Bayar',
                        'failed' => 'Gagal',
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                    \pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction::make(),
                ]),
            ]);
    }

    // ─── Pages ────────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOrders::route('/'),
            'view'   => Pages\ViewOrder::route('/{record}'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
