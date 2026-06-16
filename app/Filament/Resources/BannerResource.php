<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    protected static string|\UnitEnum|null $navigationGroup = 'Katalog';

    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return 'Banner';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Banner';
    }

    // ─── Form ─────────────────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Konten Banner')
                ->schema([
                    TextInput::make('title.id')
                        ->label('Judul (Indonesia)')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('title.en')
                        ->label('Judul (English)')
                        ->maxLength(255),

                    FileUpload::make('image_url')
                        ->label('Gambar Banner')
                        ->image()
                        ->required()
                        ->directory('banners')
                        ->visibility('public')
                        ->imagePreviewHeight('200')
                        ->columnSpanFull(),

                    TextInput::make('link_url')
                        ->label('Link Tujuan (URL)')
                        ->url()
                        ->placeholder('https://...')
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Pengaturan')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Tampilkan Banner')
                        ->default(true),

                    TextInput::make('sort_order')
                        ->label('Urutan Tampil')
                        ->numeric()
                        ->default(0),
                ])
                ->columns(2),
        ]);
    }

    // ─── Table ────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')
                    ->label('Preview')
                    ->width(120)
                    ->height(60),

                TextColumn::make('title_id')
                    ->label('Judul (ID)')
                    ->state(fn (Banner $record) => $record->getTranslation('title', 'id', false))
                    ->searchable(query: fn ($query, $search) => $query->whereRaw("JSON_EXTRACT(title, '$.id') LIKE ?", ["%{$search}%"]))
                    ->sortable(query: fn ($query, $direction) => $query->orderByRaw("JSON_EXTRACT(title, '$.id') $direction"))
                    ->weight('bold'),

                TextColumn::make('title_en')
                    ->label('Judul (EN)')
                    ->state(fn (Banner $record) => $record->getTranslation('title', 'en', false))
                    ->searchable(query: fn ($query, $search) => $query->whereRaw("JSON_EXTRACT(title, '$.en') LIKE ?", ["%{$search}%"]))
                    ->color('gray'),

                TextColumn::make('link_url')
                    ->label('Link')
                    ->url(fn (Banner $record) => $record->link_url)
                    ->openUrlInNewTab()
                    ->limit(40)
                    ->placeholder('—'),

                ToggleColumn::make('is_active')
                    ->label('Aktif'),

                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->recordAction(null)
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    // ─── Pages ────────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit'   => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
