<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Models\Coupon;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-ticket';

    protected static string|\UnitEnum|null $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return 'Kupon';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Kupon';
    }

    // ─── Form ─────────────────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Detail Kupon')
                ->schema([
                    TextInput::make('code')
                        ->label('Kode Kupon')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50)
                        ->placeholder('PROMO10')
                        ->helperText('Kode yang digunakan pelanggan saat checkout.'),

                    Select::make('discount_type')
                        ->label('Jenis Diskon')
                        ->options([
                            'percentage' => 'Persentase (%)',
                            'fixed'      => 'Nominal Tetap (Rp)',
                        ])
                        ->required()
                        ->live(),

                    TextInput::make('discount_value')
                        ->label(fn (Get $get) => $get('discount_type') === 'percentage' ? 'Nilai Diskon (%)' : 'Nilai Diskon (Rp)')
                        ->required()
                        ->numeric()
                        ->prefix(fn (Get $get) => $get('discount_type') === 'percentage' ? null : 'Rp')
                        ->suffix(fn (Get $get) => $get('discount_type') === 'percentage' ? '%' : null),

                    TextInput::make('min_purchase')
                        ->label('Minimum Pembelian (Rp)')
                        ->numeric()
                        ->prefix('Rp')
                        ->default(0),
                ])
                ->columns(2),

            Section::make('Batas Penggunaan & Masa Berlaku')
                ->schema([
                    TextInput::make('max_uses')
                        ->label('Maksimal Penggunaan')
                        ->numeric()
                        ->placeholder('Kosongkan = tidak terbatas'),

                    Toggle::make('is_active')
                        ->label('Kupon Aktif')
                        ->default(true),

                    DateTimePicker::make('valid_from')
                        ->label('Berlaku Mulai')
                        ->native(false)
                        ->displayFormat('d M Y H:i'),

                    DateTimePicker::make('valid_until')
                        ->label('Berlaku Sampai')
                        ->native(false)
                        ->displayFormat('d M Y H:i')
                        ->after('valid_from'),
                ])
                ->columns(2),
        ]);
    }

    // ─── Table ────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->copyable()
                    ->weight('bold')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('discount_type')
                    ->label('Tipe')
                    ->formatStateUsing(fn ($state) => $state === 'percentage' ? 'Persentase' : 'Nominal')
                    ->badge()
                    ->color(fn ($state) => $state === 'percentage' ? 'info' : 'success'),

                TextColumn::make('discount_value')
                    ->label('Nilai Diskon')
                    ->state(fn (Coupon $record) => $record->discount_type === 'percentage'
                        ? "{$record->discount_value}%"
                        : 'Rp '.number_format((float) $record->discount_value, 0, ',', '.')
                    ),

                TextColumn::make('min_purchase')
                    ->label('Min. Belanja')
                    ->money('IDR'),

                TextColumn::make('used_count')
                    ->label('Dipakai')
                    ->state(fn (Coupon $record) => $record->max_uses
                        ? "{$record->used_count} / {$record->max_uses}"
                        : $record->used_count
                    )
                    ->badge()
                    ->color('gray'),

                TextColumn::make('valid_until')
                    ->label('Berlaku s/d')
                    ->dateTime('d M Y')
                    ->placeholder('Tidak terbatas')
                    ->color(fn (Coupon $record) => $record->valid_until && now()->gt($record->valid_until) ? 'danger' : null),

                ToggleColumn::make('is_active')
                    ->label('Aktif'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_active')->label('Status'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    // ─── Pages ────────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit'   => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
