@props(['url'])
@php
    $settings = app(App\Settings\GeneralSettings::class);
    $companyName = $settings->company_name ?? config('app.name', 'Epoxyndo Art Lestari');
    $logoUrl = null;
    if (!empty($settings->company_logo)) {
        $disk = config('filament.default_filesystem_disk', 'public');
        $logoUrl = \Illuminate\Support\Facades\Storage::disk($disk)->url($settings->company_logo);
        if (str_starts_with($logoUrl, '/')) {
            $logoUrl = rtrim(config('app.url'), '/') . $logoUrl;
        }
    }
@endphp
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if ($logoUrl)
<img src="{{ $logoUrl }}" class="logo" alt="{{ $companyName }}" style="height: auto; max-height: 50px; width: auto;">
@else
<span style="font-size: 20px; font-weight: bold; color: #008943;">{{ $companyName }}</span>
@endif
</a>
</td>
</tr>
