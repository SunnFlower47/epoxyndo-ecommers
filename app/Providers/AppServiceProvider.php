<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        // Cloudflare R2 does NOT support server-side CopyObject with ACLs.
        // We override saveUploadedFileUsing to read the temp file content directly
        // and upload it fresh to S3, completely bypassing the CopyObject operation.
        \Filament\Forms\Components\FileUpload::configureUsing(function (\Filament\Forms\Components\FileUpload $upload) {
            $upload
                ->moveFiles(false)
                ->saveUploadedFileUsing(function (
                    \Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file,
                ) use ($upload): string {
                    $directory = $upload->getDirectory() ?? 'uploads';
                    $disk      = $upload->getDiskName();
                    $ext       = $file->getClientOriginalExtension();
                    $filename  = (string) \Illuminate\Support\Str::ulid() . ($ext ? '.' . $ext : '');
                    $path      = ltrim($directory . '/' . $filename, '/');

                    // Read raw content and stream directly to S3/R2 — no CopyObject call
                    \Illuminate\Support\Facades\Storage::disk($disk)->put(
                        $path,
                        $file->get(),
                    );

                    return $path;
                });
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
