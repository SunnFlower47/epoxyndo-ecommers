<?php

namespace App\Filament\Resources\Campaigns;

use App\Filament\Resources\Campaigns\Pages\ManageCampaigns;
use App\Models\Campaign;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static string|\UnitEnum|null $navigationGroup = 'Email & Broadcast';

    public static function getModelLabel(): string
    {
        return 'Email Campaign';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Daftar Campaigns';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('subject')
                    ->label('Judul / Subjek Email')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                
                \Filament\Forms\Components\RichEditor::make('body')
                    ->label('Isi Konten Email (HTML)')
                    ->fileAttachmentsDirectory('campaigns')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('subject')
                    ->label('Judul Campaign')
                    ->searchable()
                    ->weight('bold'),
                
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'draft' => 'gray',
                        'sending' => 'warning',
                        'sent' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                
                \Filament\Tables\Columns\TextColumn::make('sent_count')
                    ->label('Terkirim / Total')
                    ->formatStateUsing(fn ($record) => "{$record->sent_count} / {$record->total_recipients}"),
                
                \Filament\Tables\Columns\TextColumn::make('sent_at')
                    ->label('Tanggal Kirim')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                \Filament\Actions\Action::make('send_campaign')
                    ->label('Kirim Broadcast')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Kirim Email Broadcast')
                    ->modalDescription('Apakah Anda yakin ingin mengirim campaign ini ke semua subscriber aktif? Proses ini akan berjalan di background (antrean).')
                    ->visible(fn (\App\Models\Campaign $record) => $record->status === 'draft' || $record->status === 'failed')
                    ->action(function (\App\Models\Campaign $record) {
                        // Nanti akan mendispatch Job
                        $record->update([
                            'status' => 'sending',
                            'total_recipients' => \App\Models\Subscriber::where('is_active', true)->count(),
                            'sent_count' => 0,
                        ]);
                        
                        \App\Jobs\SendCampaignJob::dispatch($record);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Broadcast Sedang Diproses!')
                            ->body('Sistem sedang mengirim email ke seluruh subscriber aktif di latar belakang.')
                            ->success()
                            ->send();
                    }),
                EditAction::make()
                    ->visible(fn (\App\Models\Campaign $record) => $record->status === 'draft'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCampaigns::route('/'),
        ];
    }
}
