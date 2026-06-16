<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static string|\UnitEnum|null $navigationGroup = 'Katalog';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return 'Kategori';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Kategori';
    }

    // ─── Form ─────────────────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Kategori')
                ->schema([
                    Select::make('parent_id')
                        ->label('Kategori Induk')
                        ->relationship('parent', 'name')
                        ->searchable()
                        ->preload()
                        ->placeholder('Tanpa induk (kategori utama)')
                        ->columnSpanFull(),

                    TextInput::make('name.id')
                        ->label('Nama (Indonesia)')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (string $operation, $state, \Filament\Schemas\Components\Utilities\Set $set) {
                            if ($operation !== 'create') {
                                return;
                            }
                            $set('slug', Str::slug($state));
                        }),

                    TextInput::make('name.en')
                        ->label('Nama (English)')
                        ->maxLength(255),

                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->columnSpanFull(),

                    Textarea::make('description.id')
                        ->label('Deskripsi (Indonesia)')
                        ->rows(3),

                    Textarea::make('description.en')
                        ->label('Deskripsi (English)')
                        ->rows(3),
                ])
                ->columns(2),

            Section::make('Pengaturan')
                ->schema([
                    FileUpload::make('image')
                        ->label('Gambar Kategori')
                        ->image()
                        ->directory('categories')
                        ->visibility('public')
                        ->columnSpanFull(),

                    Toggle::make('is_active')
                        ->label('Aktif')
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
                ImageColumn::make('image')
                    ->label('Gambar')
                    ->circular()
                    ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=C&color=008943&background=e6f4ee'),

                TextColumn::make('name_id')
                    ->label('Nama (ID)')
                    ->state(fn (Category $record) => $record->getTranslation('name', 'id', false))
                    ->searchable(query: fn ($query, $search) => $query->whereRaw("JSON_EXTRACT(name, '$.id') LIKE ?", ["%{$search}%"]))
                    ->sortable(query: fn ($query, $direction) => $query->orderByRaw("JSON_EXTRACT(name, '$.id') $direction"))
                    ->weight('bold'),

                TextColumn::make('name_en')
                    ->label('Nama (EN)')
                    ->state(fn (Category $record) => $record->getTranslation('name', 'en', false))
                    ->searchable(query: fn ($query, $search) => $query->whereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"]))
                    ->color('gray'),

                TextColumn::make('parent_name')
                    ->label('Kategori Induk')
                    ->state(fn (Category $record) => $record->parent ? $record->parent->getTranslation('name', 'id', false) : '—')
                    ->placeholder('—')
                    ->badge()
                    ->color('info'),

                TextColumn::make('products_count')
                    ->label('Produk')
                    ->counts('products')
                    ->badge()
                    ->color('success'),

                ToggleColumn::make('is_active')
                    ->label('Aktif'),

                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TernaryFilter::make('is_active')->label('Status Aktif'),
                SelectFilter::make('parent_id')
                    ->label('Kategori Induk')
                    ->relationship('parent', 'name'),
            ])
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
            'index'  => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit'   => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
