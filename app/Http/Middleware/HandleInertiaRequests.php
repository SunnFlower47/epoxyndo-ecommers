<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Storage;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $generalSettings = app(GeneralSettings::class);
        
        $disk = config('filament.default_filesystem_disk', 'public');
        $isS3 = config("filesystems.disks.{$disk}.driver") === 's3';

        $logoUrl = null;
        if ($generalSettings->company_logo) {
            $logoUrl = $isS3 
                ? Storage::disk($disk)->temporaryUrl($generalSettings->company_logo, now()->addMinutes(60))
                : Storage::disk($disk)->url($generalSettings->company_logo);
        }

        $locale = $request->cookie('locale', config('app.locale', 'id'));
        app()->setLocale($locale);

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'locale' => $locale,
            'midtrans_client_key' => config('services.midtrans.client_key'),
            'midtrans_is_production' => config('services.midtrans.is_production'),
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'snapToken' => $request->session()->get('snapToken'),
                'orderNumber' => $request->session()->get('orderNumber'),
            ],
            'general_settings' => [
                'company_name' => $generalSettings->company_name,
                'company_logo' => $logoUrl,
                'client_logos' => \App\Models\Partner::where('is_active', true)
                    ->orderBy('sort_order')
                    ->get()
                    ->map(function($partner) use ($disk, $isS3) {
                        return [
                            'id' => $partner->id,
                            'name' => $partner->name,
                            'logo' => $isS3 
                                ? Storage::disk($disk)->temporaryUrl($partner->logo, now()->addMinutes(60))
                                : Storage::disk($disk)->url($partner->logo)
                        ];
                    })->toArray(),
                'company_address' => $generalSettings->company_address,
                'support_phone' => $generalSettings->support_phone,
                'support_email' => $generalSettings->support_email,
                'tax_percentage' => $generalSettings->tax_percentage,
                'social_media' => $generalSettings->social_media,
            ],
            'shared_categories' => \App\Models\Category::whereNull('parent_id')
                ->with('children')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(function($category) use ($disk, $isS3) {
                    if ($category->image) {
                        $category->image = $isS3 
                            ? Storage::disk($disk)->temporaryUrl($category->image, now()->addMinutes(60))
                            : Storage::disk($disk)->url($category->image);
                    }
                    return $category;
                }),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
