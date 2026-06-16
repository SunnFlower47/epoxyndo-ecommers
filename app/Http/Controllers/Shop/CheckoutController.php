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
                    "quantity" => $item["quantity"],
                    "unit_price" => $price,
                    "subtotal" => $itemSubtotal,
                ];
            }

            $shippingCost = 0; // In a real app, calculate this based on courier & city
            $grandTotal = $subtotal + $shippingCost;

            $order = Order::create([
                "user_id" => auth()->id(), // null if guest
                "order_number" => "ORD-" . strtoupper(Str::random(10)),
                "status" => Order::STATUS_PENDING,
                "subtotal" => $subtotal,
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

            DB::commit();

            try {
                \Illuminate\Support\Facades\Mail::to($order->customer_email ?? $order->user?->email)
                    ->queue(new \App\Mail\OrderPlacedMail($order));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send OrderPlacedMail: ' . $e->getMessage());
            }

            return redirect()->route("home")->with("success", "Pesanan berhasil dibuat dengan nomor: " . $order->order_number);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with("error", "Terjadi kesalahan: " . $e->getMessage());
        }
    }
}

