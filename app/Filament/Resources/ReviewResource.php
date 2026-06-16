<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Models\Review;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-star';

    protected static string|\UnitEnum|null $navigationGroup = 'Review';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return 'Ulasan';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Ulasan';
    }

    // ─── Form ─────────────────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->schema([
                    Toggle::make('is_approved')
                        ->label('Setujui Ulasan')
                        ->helperText('Ulasan hanya tampil di toko jika disetujui.'),

                    Textarea::make('comment')
                        ->label('Komentar')
                        ->rows(4)
                        ->disabled(),
                ]),
        ]);
    }

    // ─── Table ────────────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Pembeli')
                    ->searchable(),

                TextColumn::make('product_name')
                    ->label('Produk')
                    ->state(fn (Review $record) => $record->product ? $record->product->getTranslation('name', 'id', false) : '—')
                    ->searchable(query: fn ($query, $search) => $query->whereHas('product', fn ($q) => $q->whereRaw("JSON_EXTRACT(name, '$.id') LIKE ?", ["%{$search}%"])))
                    ->limit(30),

                TextColumn::make('rating')
                    ->label('Rating')
                    ->state(fn (Review $record) => str_repeat('★', $record->rating).str_repeat('☆', 5 - $record->rating))
                    ->color(fn (Review $record) => match (true) {
                        $record->rating >= 4 => 'success',
                        $record->rating >= 3 => 'warning',
                        default              => 'danger',
                    }),

                TextColumn::make('comment')
                    ->label('Komentar')
                    ->limit(60)
                    ->placeholder('—'),

                ToggleColumn::make('is_approved')
                    ->label('Disetujui'),

                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_approved')->label('Status Persetujuan'),
            ])
            ->actions([
                EditAction::make()->label('Moderasi'),
                DeleteAction::make(),
            ]);
    }

    // ─── Pages ────────────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'edit'  => Pages\EditReview::route('/{record}/edit'),
        ];
    }
}
