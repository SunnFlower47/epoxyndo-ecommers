<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Filament\Actions\Action;

class BackupPage extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?string $navigationLabel = 'Backup Database';
    protected static ?string $title = 'Backup Database';
    protected static string | \UnitEnum | null $navigationGroup = 'Sistem';
    protected static ?int $navigationSort = 300;

    protected string $view = 'filament.pages.backup-page';

    public $backups = [];

    public function mount()
    {
        $this->loadBackups();
    }

    public function loadBackups()
    {
        $disk = Storage::disk(config('backup.backup.destination.disks')[0] ?? 'local');
        $files = $disk->files(config('backup.backup.name'));

        $this->backups = collect($files)->filter(function ($file) {
            return str_ends_with($file, '.zip');
        })->map(function ($file) use ($disk) {
            return [
                'path' => $file,
                'name' => basename($file),
                'size' => round($disk->size($file) / 1048576, 2) . ' MB',
                'date' => date('Y-m-d H:i:s', $disk->lastModified($file)),
            ];
        })->sortByDesc('date')->values()->toArray();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createBackup')
                ->label('Buat Backup Baru')
                ->icon('heroicon-o-plus')
                ->action('runBackup')
                ->color('primary')
                ->requiresConfirmation(),
        ];
    }

    public function runBackup()
    {
        try {
            // Set max execution time since backups can take a while
            set_time_limit(300);
            
            Artisan::call('backup:run', ['--only-db' => true]);
            
            $this->loadBackups();
            
            Notification::make()
                ->title('Backup berhasil dibuat!')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal membuat backup')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function downloadBackup($path)
    {
        $disk = Storage::disk(config('backup.backup.destination.disks')[0] ?? 'local');
        return response()->download($disk->path($path));
    }

    public function deleteBackup($path)
    {
        $disk = Storage::disk(config('backup.backup.destination.disks')[0] ?? 'local');
        $disk->delete($path);
        
        $this->loadBackups();
        
        Notification::make()
            ->title('Backup berhasil dihapus!')
            ->success()
            ->send();
    }
}
