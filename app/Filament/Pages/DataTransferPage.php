<?php

namespace App\Filament\Pages;

use App\Exports\ProductsExport;
use App\Imports\ProductsPreviewImport;
use App\Imports\ProductsImport;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Facades\Storage;

class DataTransferPage extends Page implements HasForms
{
    use InteractsWithForms;
    use \Livewire\WithFileUploads;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationLabel = 'Export / Import Data';
    protected static ?string $title = 'Data Transfer';
    protected static ?string $slug = 'data-transfer';
    protected static string | \UnitEnum | null $navigationGroup = 'Sistem';

    protected string $view = 'filament.pages.data-transfer-page';

    public $activeTab = 'export';
    public $exportResource = 'products';

    // Import state
    public $importResource = 'products';
    public $importFile = null;
    public $previewValidCount = 0;
    public $previewInvalidCount = 0;
    public $isPreviewing = false;
    public $fileToImportPath = null;

    public function mount()
    {
        // Initialization
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetImportState();
    }

    public function resetImportState()
    {
        $this->importFile = null;
        $this->isPreviewing = false;
        $this->previewValidCount = 0;
        $this->previewInvalidCount = 0;
        $this->fileToImportPath = null;
    }

    public function processPreview()
    {
        if (!$this->importFile) {
            Notification::make()->title('Harap unggah file terlebih dahulu')->danger()->send();
            return;
        }

        try {
            // Get the temporary uploaded file
            $file = $this->importFile;
            
            if (is_array($file)) {
                $file = $file[array_key_first($file)];
            }
            
            // Check if it's a real TemporaryUploadedFile
            if (!($file instanceof TemporaryUploadedFile)) {
                 Notification::make()->title('File tidak valid')->danger()->send();
                 return;
            }

            $import = new ProductsPreviewImport();
            Excel::import($import, $file->getRealPath());

            $this->previewValidCount = $import->validCount;
            $this->previewInvalidCount = $import->invalidCount;
            $this->isPreviewing = true;
            
            // Store the file permanently so the background job can access it later
            $this->fileToImportPath = $file->store('imports', 'local');
            
            // Dispatch browser event to show success animation for preview
            $this->dispatch('show-lottie', name: 'success');

        } catch (\Exception $e) {
            Notification::make()->title('Gagal membaca file: ' . $e->getMessage())->danger()->send();
        }
    }

    public function executeImport()
    {
        if (!$this->fileToImportPath) {
            return;
        }

        try {
            // Because chunk reading can be slow, we'll queue it if possible
            // But for simplicity, we'll run it synchronously here and show a loader
            Excel::import(new ProductsImport(), Storage::disk('local')->path($this->fileToImportPath));
            
            $this->resetImportState();
            Notification::make()->title('Data berhasil di-import')->success()->send();
            
            // Trigger success animation
            $this->dispatch('show-lottie', name: 'success');

        } catch (\Exception $e) {
            Notification::make()->title('Gagal meng-import: ' . $e->getMessage())->danger()->send();
        }
    }

    public function executeExport()
    {
        // Trigger export file download
        if ($this->exportResource === 'products') {
            // Dispatch browser event to show loading/success animation
            $this->dispatch('show-lottie', name: 'success');
            return Excel::download(new ProductsExport(), 'products_export_' . date('Y_m_d_H_i') . '.xlsx');
        }
        
        Notification::make()->title('Fitur belum tersedia untuk resource ini')->warning()->send();
    }
}
