<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Services\BiteshipService;
use App\Settings\GeneralSettings;

class CheckoutController extends Controller
{
    public function index()
    {
        return Inertia::render("checkout");
    }

    public function process(Request $request)
    {
        $request->validate([
            "items" => "required|array|min:1",
            "items.*.product_id" => "required|exists:products,id",
            "items.*.quantity" => "required|integer|min:1",
            "customer_name" => "required|string|max:255",
            "customer_email" => "required|email|max:255",
            "customer_phone" => "required|string|max:20",
            "shipping_address" => "required|string",
            "city" => "required|string",
            "postal_code" => "required|string",
            "courier" => "nullable|string",
            "courier_service" => "nullable|string",
        ]);

        try {
            DB::beginTransaction();

            $subtotal = 0;
            $orderItems = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item["product_id"]);
                $price = $product->final_price; 
                
                $itemSubtotal = $price * $item["quantity"];
                $subtotal += $itemSubtotal;

                $orderItems[] = [
                    "product_id" => $product->id,
                    "product_name" => $product->name,
                    "product_sku" => $product->sku ?? '-',
                    "price" => $price,
                    "quantity" => $item["quantity"],
                    "total" => $itemSubtotal,
                ];
            }

            $shippingCost = $request->shipping_cost ?? 0;
            $taxPercentage = app(GeneralSettings::class)->tax_percentage ?? 11;
            $taxAmount = ($subtotal * $taxPercentage) / 100;
            
            $grandTotal = $subtotal + $shippingCost + $taxAmount;

            $order = Order::create([
                "user_id" => auth()->id(), // null if guest
                "order_number" => "ORD-" . strtoupper(Str::random(10)),
                "status" => Order::STATUS_PENDING,
                "subtotal" => $subtotal,
                "tax_amount" => $taxAmount,
                "shipping_cost" => $shippingCost,
                "grand_total" => $grandTotal,
                "customer_name" => $request->customer_name,
                "customer_email" => $request->customer_email,
                "customer_phone" => $request->customer_phone,
                "shipping_address" => [
                    "address" => $request->shipping_address,
                    "city" => $request->city,
                    "postal_code" => $request->postal_code,
                ],
                "courier" => $request->courier ?? "REGULAR",
                "courier_service" => $request->courier_service ?? "Standard",
            ]);

            foreach ($orderItems as $itemData) {
                $order->items()->create($itemData);
            }

            try {
                \App\Models\Subscriber::updateOrCreate(
                    ['email' => $request->customer_email],
                    ['name' => $request->customer_name, 'is_active' => true]
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to subscribe customer on checkout: ' . $e->getMessage());
            }

            // Generate Midtrans Snap Token
            \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
            \Midtrans\Config::$isProduction = config('services.midtrans.is_production');
            \Midtrans\Config::$isSanitized = config('services.midtrans.is_sanitized');
            \Midtrans\Config::$is3ds = config('services.midtrans.is_3ds');

            $params = [
                'transaction_details' => [
                    'order_id' => $order->order_number,
                    'gross_amount' => $order->grand_total,
                ],
                'customer_details' => [
                    'first_name' => $order->customer_name,
                    'email' => $order->customer_email,
                    'phone' => $order->customer_phone,
                ],
            ];
            
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            $order->update(['snap_token' => $snapToken]);

            DB::commit();

            try {
                \Illuminate\Support\Facades\Mail::to($order->customer_email ?? $order->user?->email)
                    ->queue(new \App\Mail\OrderPlacedMail($order));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send OrderPlacedMail: ' . $e->getMessage());
            }

            return back()->with([
                "success" => "Pesanan berhasil dibuat dengan nomor: " . $order->order_number,
                "snapToken" => $snapToken,
                "orderNumber" => $order->order_number
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with("error", "Terjadi kesalahan: " . $e->getMessage());
        }
    }

    public function shippingRates(Request $request, BiteshipService $biteship)
    {
        $request->validate([
            'destination_postal_code' => 'nullable|string',
            'destination_latitude' => 'nullable|string',
            'destination_longitude' => 'nullable|string',
            'items' => 'required|array',
        ]);

        $settings = app(GeneralSettings::class);
        $originLat = $settings->warehouse_latitude;
        $originLng = $settings->warehouse_longitude;

        // Hitung total berat dan periksa produk bulky
        $totalWeight = 0;
        $isBulky = false;
        
        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            if ($product) {
                $totalWeight += ($product->weight ?? 1000) * $item['quantity'];
                if ($product->is_bulky) {
                    $isBulky = true;
                }
            }
        }

        $payload = [
            'origin_latitude' => (float) $originLat,
            'origin_longitude' => (float) $originLng,
            'destination_latitude' => $request->destination_latitude ? (float) $request->destination_latitude : null,
            'destination_longitude' => $request->destination_longitude ? (float) $request->destination_longitude : null,
            'couriers' => $isBulky ? 'jne,sicepat,indah,sentral,dakota' : 'jne,sicepat,jnt,ninja,anteraja,tiki,pos',
            'items' => [
                [
                    'name' => 'Pesanan Epoxyndo',
                    'value' => 100000, // Dummy value for insurance estimation
                    'weight' => $totalWeight > 0 ? $totalWeight : 1000,
                    'quantity' => 1
                ]
            ]
        ];

        // Jika user hanya mengirim postal code, Biteship API v1 mungkin butuh mapping area id, 
        // tapi kita bisa coba kirim postal_code jika latitude/longitude tidak ada.
        if (!$payload['destination_latitude']) {
            unset($payload['destination_latitude']);
            unset($payload['destination_longitude']);
            unset($payload['origin_latitude']);
            unset($payload['origin_longitude']);
            $payload['destination_postal_code'] = $request->destination_postal_code;
            $payload['origin_postal_code'] = '10110'; // Fallback postal code gudang jika tidak pakai lat/lng
        }

        try {
            $rates = $biteship->getRates($payload);
            
            // Filter services for bulky items to only show Cargo options
            if ($isBulky && isset($rates['pricing'])) {
                $cargoTypes = ['jtr', 'gokil', 'indah', 'dakota', 'sentral', 'cargo'];
                $filteredPricing = array_filter($rates['pricing'], function($rate) use ($cargoTypes) {
                    $type = strtolower($rate['type'] ?? '');
                    $company = strtolower($rate['company'] ?? '');
                    // Keep if the service type or company is a known cargo
                    return in_array($type, $cargoTypes) || in_array($company, ['indah', 'dakota', 'sentral']);
                });
                
                // If filtering removed everything (maybe unsupported), fallback to the original list or just empty
                if (count($filteredPricing) > 0) {
                    $rates['pricing'] = array_values($filteredPricing);
                }
            }

            return response()->json($rates);
        } catch (\Exception $e) {
            // Jika Biteship error (contoh: saldo habis), kembalikan rate dummy sementara agar checkout tidak terhenti
            return response()->json([
                'pricing' => [
                    [
                        'company' => 'jne',
                        'type' => 'REG',
                        'price' => 15000,
                        'estimated_delivery' => '2-3 hari'
                    ],
                    [
                        'company' => 'sicepat',
                        'type' => 'HALU',
                        'price' => 12000,
                        'estimated_delivery' => '3-5 hari'
                    ]
                ]
            ]);
        }
    }

    public function midtransCallback(Request $request)
    {
        $serverKey = config('services.midtrans.server_key');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);
        
        if ($hashed == $request->signature_key) {
            $order = Order::where('order_number', $request->order_id)->first();
            if ($order) {
                if ($request->transaction_status == 'capture' || $request->transaction_status == 'settlement') {
                    $order->update([
                        'status' => Order::STATUS_PROCESSING,
                        'payment_status' => 'Lunas'
                    ]);
                } else if ($request->transaction_status == 'cancel' || $request->transaction_status == 'deny' || $request->transaction_status == 'expire') {
                    $order->update([
                        'status' => Order::STATUS_CANCELLED,
                        'payment_status' => 'Gagal/Batal'
                    ]);
                } else if ($request->transaction_status == 'pending') {
                    $order->update([
                        'payment_status' => 'Belum Bayar'
                    ]);
                }
            }
        }
        
        return response()->json(['status' => 'success']);
    }
}

