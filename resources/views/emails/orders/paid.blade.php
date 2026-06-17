<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Diterima - {{ $order->order_number }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #F8FAFC; font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-text-size-adjust: none; color: #334155; line-height: 1.6;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #F8FAFC; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #E2E8F0; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);">
                    <!-- Top Brand Header Bar -->
                    <tr>
                        <td style="background-color: #008943; height: 6px;"></td>
                    </tr>
                    
                    <!-- Header Logo / Name -->
                    <tr>
                        <td align="center" style="padding: 30px 40px 20px 40px; border-bottom: 1px solid #F1F5F9;">
                            @php
                                $settings = app(App\Settings\GeneralSettings::class);
                                $companyName = $settings->company_name ?? 'Epoxyndo Art Lestari';
                                $logoUrl = null;
                                if (!empty($settings->company_logo)) {
                                    $disk = config('filament.default_filesystem_disk', 'public');
                                    $logoUrl = \Illuminate\Support\Facades\Storage::disk($disk)->url($settings->company_logo);
                                    if (str_starts_with($logoUrl, '/')) {
                                        $logoUrl = rtrim(config('app.url'), '/') . $logoUrl;
                                    }
                                }
                            @endphp
                            <a href="{{ config('app.url') }}" style="text-decoration: none; display: inline-block;">
                                @if ($logoUrl)
                                    <img src="{{ $logoUrl }}" alt="{{ $companyName }}" style="max-height: 45px; height: auto; display: block; border: 0;">
                                @else
                                    <span style="font-size: 22px; font-weight: 800; color: #008943; letter-spacing: -0.5px;">{{ $companyName }}</span>
                                @endif
                            </a>
                        </td>
                    </tr>
                    
                    <!-- Content Body -->
                    <tr>
                        <td style="padding: 40px 40px 20px 40px; font-size: 16px; color: #334155;">
            <h2 style="color: #0f172a; margin-top: 0;">Halo, {{ $order->customer_name ?? $order->user?->name }}!</h2>
            <p>Terima kasih telah berbelanja di PT Epoxyndo Art Lestari. Kami dengan senang hati menginformasikan bahwa <strong>pembayaran Anda untuk pesanan **{{ $order->order_number }}** telah BERHASIL kami terima</strong>. Saat ini pesanan Anda sedang kami proses untuk segera dikirimkan.</p>

            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin: 25px 0;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #64748b;">Nomor Pesanan</td>
                        <td style="padding: 8px 0; text-align: right; font-weight: bold; color: #0f172a;">{{ $order->order_number }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #64748b;">Status Pembayaran</td>
                        <td style="padding: 8px 0; text-align: right;">
                            <span style="background-color: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: bold;">Lunas / Terverifikasi</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #64748b; border-bottom: 1px dashed #cbd5e1;">Metode Pembayaran</td>
                        <td style="padding: 8px 0; text-align: right; font-weight: bold; color: #475569; border-bottom: 1px dashed #cbd5e1;">TRANSFER / GATEWAY</td>
                    </tr>
                    <tr>
                        <td style="padding: 16px 0 8px 0; font-weight: bold; color: #0f172a;">Total Pembayaran</td>
                        <td style="padding: 16px 0 8px 0; text-align: right; font-weight: bold; color: #059669; font-size: 18px;">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>                
                            
                            <!-- Call to Action Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 24px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ config('app.url') }}" style="background-color: #008943; color: #ffffff; text-decoration: none; display: inline-block; padding: 12px 30px; font-weight: 700; font-size: 15px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 137, 67, 0.15);">Lihat Detail Pesanan</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Footer Signature -->
                    <tr>
                        <td style="padding: 0 40px 30px 40px; font-size: 15px; color: #64748B;">
                            <p style="margin: 0 0 4px 0;">Terima kasih,</p>
                            <p style="margin: 0; font-weight: 700; color: #0F172A;">Tim {{ $companyName }}</p>
                        </td>
                    </tr>
                    
                    <!-- Company Contact Info & Social Footer -->
                    <tr>
                        <td align="center" style="background-color: #F8FAFC; padding: 30px 40px; border-top: 1px solid #E2E8F0; font-size: 12px; color: #94A3B8; text-align: center;">
                            <p style="margin: 0 0 10px 0; font-weight: 600; color: #64748B; font-size: 13px;">{{ $companyName }}</p>
                            @if(!empty($settings->company_address))
                                <p style="margin: 0 0 15px 0; line-height: 1.5;">{{ $settings->company_address }}</p>
                            @endif
                            
                            <p style="margin: 0; line-height: 1.8;">
                                @if(!empty($settings->support_email))
                                    Email: <a href="mailto:{{ $settings->support_email }}" style="color: #008943; text-decoration: none;">{{ $settings->support_email }}</a>
                                @endif
                                @if(!empty($settings->support_phone))
                                    &nbsp;&bull;&nbsp; Hubungi Kami: <a href="tel:{{ $settings->support_phone }}" style="color: #008943; text-decoration: none;">{{ $settings->support_phone }}</a>
                                @endif
                            </p>
                            
                            <p style="margin: 15px 0 0 0; font-size: 11px; color: #CBD5E1;">
                                &copy; {{ date('Y') }} {{ $companyName }}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
