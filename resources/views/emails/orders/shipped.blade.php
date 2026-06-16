<x-mail::message>
# Halo {{ $order->customer_name ?? $order->user?->name ?? 'Pelanggan' }},

Kabar gembira! Pesanan Anda dengan nomor **{{ $order->order_number }}** telah diserahkan ke pihak kurir dan sedang dalam perjalanan.

## Detail Pengiriman:
- **Kurir:** {{ strtoupper($order->courier) }}
- **Layanan:** {{ strtoupper($order->courier_service) }}
- **Nomor Resi:** **{{ $shipment->tracking_number ?? 'Menunggu Update Kurir' }}**

Anda bisa melacak pengiriman Anda menggunakan nomor resi di atas pada website resmi kurir atau melalui tautan pelacakan Biteship di bawah ini jika tersedia.

@if($shipment->tracking_url)
<x-mail::button :url="$shipment->tracking_url">
Lacak Pesanan
</x-mail::button>
@endif

Terima kasih atas kepercayaannya berbelanja di {{ config('app.name') }}.

Salam hangat,<br>
Tim {{ config('app.name') }}
</x-mail::message>
