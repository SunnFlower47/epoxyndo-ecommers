<x-mail::message>
# Halo {{ $order->customer_name ?? $order->user?->name ?? 'Pelanggan' }},

Terima kasih telah berbelanja di {{ config('app.name') }}.
Pesanan Anda dengan nomor **{{ $order->order_number }}** telah kami terima dan sedang menunggu pembayaran.

## Detail Pesanan:
- **Total:** Rp {{ number_format($order->grand_total, 0, ',', '.') }}
- **Status:** Menunggu Pembayaran

<x-mail::button :url="url('/')">
Lihat Pesanan
</x-mail::button>

Terima kasih,<br>
Tim {{ config('app.name') }}
</x-mail::message>
