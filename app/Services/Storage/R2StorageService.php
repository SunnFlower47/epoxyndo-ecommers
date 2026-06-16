<?php

namespace App\Services\Storage;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class R2StorageService
{
    protected string $disk = 'r2';

    /**
     * Upload product image or PDF resi to Cloudflare R2.
     */
    public function upload(UploadedFile $file, string $folder = 'products'): string
    {
        $path = $file->store($folder, $this->disk);
        return Storage::disk($this->disk)->url($path);
    }

    /**
     * Upload file from a raw content string (like pdf receipts).
     */
    public function uploadRaw(string $content, string $filename, string $folder = 'receipts'): string
    {
        $path = $folder . '/' . $filename;
        Storage::disk($this->disk)->put($path, $content);
        return Storage::disk($this->disk)->url($path);
    }

    /**
     * Delete file from Cloudflare R2.
     */
    public function delete(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH);
        // Remove leading slash if any
        $path = ltrim($path, '/');
        
        // Extract R2 bucket path
        return Storage::disk($this->disk)->delete($path);
    }
}
