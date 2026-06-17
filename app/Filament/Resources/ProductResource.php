<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static string|\UnitEnum|null $navigationGroup = 'Katalog';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return 'Produk';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Produk';
    }

    // ─── Form ─────────────────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            // ── Kolom Kiri (2/3 lebar) ─────────────────────────────────────
            Group::make()->schema([

                Section::make('Informasi Produk')
                    ->schema([
                        TextInput::make('name.id')
                            ->label('Nama Produk (Indonesia)')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Set $set) {
                                if ($operation !== 'create') {
                                    return;
                                }
                                $set('slug', Str::slug($state));
                                $set('sku', strtoupper(Str::random(3)).'-'.strtoupper(Str::slug(Str::limit($state, 6, ''))));
                            }),

                        TextInput::make('name.en')
                            ->label('Nama Produk (English)')
                            ->maxLength(255),

                        TextInput::make('slug')
                            ->label('Slug URL')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('sku')
                            ->label('SKU')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(100),

                        MarkdownEditor::make('description.id')
                            ->label('Deskripsi (Indonesia)')
                            ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link'])
                            ->columnSpanFull(),

                        MarkdownEditor::make('description.en')
                            ->label('Deskripsi (English)')
                            ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link'])
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Harga & Diskon')
                    ->schema([
                        TextInput::make('price')
                            ->label('Harga Normal (Rp)')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->step(1000),

                        Select::make('discount_type')
                            ->label('Jenis Diskon')
                            ->options([
                                'percentage' => 'Persentase (%)',
                                'fixed'      => 'Nominal Tetap (Rp)',
                            ])
                            ->live()
                            ->placeholder('Tidak ada diskon'),

                        TextInput::make('discount_value')
                            ->label(fn (Get $get) => $get('discount_type') === 'percentage' ? 'Nilai Diskon (%)' : 'Nilai Diskon (Rp)')
                            ->numeric()
                            ->visible(fn (Get $get) => filled($get('discount_type')))
                            ->prefix(fn (Get $get) => $get('discount_type') === 'percentage' ? null : 'Rp')
                            ->suffix(fn (Get $get) => $get('discount_type') === 'percentage' ? '%' : null),

                        Fieldset::make('Flash Sale (Opsional)')
                            ->schema([
                                DateTimePicker::make('discount_start')
                                    ->label('Mulai Flash Sale')
                                    ->native(false)
                                    ->displayFormat('d M Y H:i'),

                                DateTimePicker::make('discount_end')
                                    ->label('Selesai Flash Sale')
                                    ->native(false)
                                    ->displayFormat('d M Y H:i')
                                    ->after('discount_start'),
                            ])
                            ->columns(2)
                            ->visible(fn (Get $get) => filled($get('discount_type'))),
                    ])
                    ->columns(2),

                Section::make('Foto Produk')
                    ->schema([
                        \Filament\Forms\Components\Repeater::make('images')
                            ->relationship('images')
                            ->schema([
                                \Filament\Forms\Components\FileUpload::make('image_url')
                                    ->label('Foto')
                                    ->image()
                                    ->directory('products')
                                    ->visibility('public')
                                    ->required(),
                            ])
                            ->grid(3)
                            ->orderColumn('sort_order')
                            ->label('Daftar Foto Produk')
                            ->columnSpanFull(),
                    ]),

                Section::make('Varian Produk (Berat & Harga)')
                    ->description('Setiap varian mewakili pilihan berat berbeda dengan harga & stok masing-masing.')
                    ->schema([
                        Repeater::make('variants')
                            ->relationship('variants')
                            ->label('Daftar Varian')
                            ->schema([
                                TextInput::make('label')
                                    ->label('Label Varian')
                                    ->placeholder('mis. 1 kg, 5 kg, 25 kg')
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('sku')
                                    ->label('SKU Varian')
                                    ->placeholder('Otomatis jika kosong')
                                    ->columnSpan(1),

                                TextInput::make('price')
                                    ->label('Harga')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->columnSpan(1),

                                TextInput::make('stock')
                                    ->label('Stok')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->columnSpan(1),

                                TextInput::make('weight')
                                    ->label('Berat (gram)')
                                    ->numeric()
                                    ->suffix('gr')
                                    ->helperText('Digunakan untuk kalkulasi ongkir')
                                    ->columnSpan(1),

                                Toggle::make('is_bulky')
                                    ->label('Kargo / Bulky')
                                    ->helperText('Aktifkan jika varian ini perlu kurir kargo')
                                    ->columnSpan(1),

                                Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true)
                                    ->columnSpan(1),
                            ])
                            ->columns(2)
                            ->orderColumn('sort_order')
                            ->addActionLabel('+ Tambah Varian')
                            ->collapsible()
                            ->columnSpanFull(),
                    ]),

            ])->columnSpan(2),

            // ── Kolom Kanan (1/3 lebar) ────────────────────────────────────
            Group::make()->schema([

                Section::make('Status')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Produk Aktif')
                            ->default(true),

                        Toggle::make('is_bulky')
                            ->label('Produk Besar / Berat Lebih'),
                    ]),

                Section::make('Sistem Pre-Order')
                    ->schema([
                        Toggle::make('is_preorder')
                            ->label('Produk Pre-Order')
                            ->live(),

                        TextInput::make('preorder_days')
                            ->label('Masa Pengerjaan (Hari)')
                            ->numeric()
                            ->visible(fn (Get $get) => $get('is_preorder'))
                            ->required(fn (Get $get) => $get('is_preorder'))
                            ->suffix('hari'),
                    ]),

                Section::make('Kategori')
                    ->schema([
                        Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                    ]),

                Section::make('Stok & Pengiriman')
                    ->schema([
                        TextInput::make('stock')
                            ->label('Stok')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        TextInput::make('moq')
                            ->label('Min. Pembelian (MOQ)')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->minValue(1),

                        TextInput::make('weight')
                            ->label('Berat (gram)')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->suffix('gr'),
                        
                        \Filament\Forms\Components\Select::make('unit_id')
                            ->label('Satuan')
                            ->relationship('unit', 'name')
                            ->required(),
                    ]),

            ])->columnSpan(1),

        ])->columns(3);
    }

    // ─── Table ────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('primaryImage.image_url')
                    ->label('')
                    ->square()
                    ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=P&color=008943&background=e6f4ee'),

                TextColumn::make('name_id')
                    ->label('Nama Produk')
                    ->state(fn (Product $record) => $record->getTranslation('name', 'id', false))
                    ->searchable(query: fn ($query, $search) => $query->whereRaw("JSON_EXTRACT(name, '$.id') LIKE ?", ["%{$search}%"]))
                    ->sortable(query: fn ($query, $direction) => $query->orderByRaw("JSON_EXTRACT(name, '$.id') $direction"))
                    ->weight('bold')
                    ->description(fn (Product $record) => $record->sku),

                TextColumn::make('category_name')
                    ->label('Kategori')
                    ->state(fn (Product $record) => $record->category ? $record->category->getTranslation('name', 'id', false) : '-')
                    ->badge()
                    ->color('info'),

                TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('final_price')
                    ->label('Harga Akhir')
                    ->money('IDR')
                    ->state(fn (Product $record) => $record->final_price)
                    ->color(fn (Product $record) => $record->has_discount ? 'success' : 'gray'),

                IconColumn::make('is_flash_sale')
                    ->label('Flash Sale')
                    ->state(fn (Product $record) => $record->is_flash_sale)
                    ->boolean()
                    ->trueIcon('heroicon-o-bolt')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning'),

                IconColumn::make('is_preorder')
                    ->label('Pre-Order')
                    ->boolean()
                    ->trueIcon('heroicon-o-clock')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('info'),

                TextColumn::make('stock')
                    ->label('Stok')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state) => match (true) {
                        $state <= 0  => 'danger',
                        $state <= 5  => 'warning',
                        default      => 'success',
                    }),

                ToggleColumn::make('is_active')
                    ->label('Aktif'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_active')->label('Status'),
                SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),
                Filter::make('low_stock')
                    ->label('Stok Menipis (≤5)')
                    ->query(fn ($query) => $query->where('stock', '<=', 5)),
                Filter::make('flash_sale')
                    ->label('Flash Sale Aktif')
                    ->query(fn ($query) => $query
                        ->whereNotNull('discount_start')
                        ->where('discount_start', '<=', now())
                        ->where('discount_end', '>=', now())
                    ),
                TernaryFilter::make('is_preorder')->label('Pre-Order'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
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
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
