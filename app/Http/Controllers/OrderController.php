<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Order;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    /**
     * Display a listing of the user's orders.
     */
    public function index(Request $request)
    {
        $orders = $request->user()->orders()
            ->with(['items.product.images', 'shipment'])
            ->orderBy('created_at', 'desc')
            ->get();

        $disk = config('filament.default_filesystem_disk', 'public');
        $isS3 = config("filesystems.disks.{$disk}.driver") === 's3';

        // Format orders to include image URLs and readable dates
        $formattedOrders = $orders->map(function ($order) use ($disk, $isS3) {
            $formattedItems = $order->items->map(function ($item) use ($disk, $isS3) {
                $imageUrl = null;
                if ($item->product && $item->product->images->isNotEmpty()) {
                    $firstImage = $item->product->images->sortBy('sort_order')->first()->image_path;
                    if ($firstImage) {
                        $imageUrl = $isS3 
                            ? Storage::disk($disk)->temporaryUrl($firstImage, now()->addMinutes(60))
                            : Storage::disk($disk)->url($firstImage);
                    }
                }
                
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->total,
                    'image_url' => $imageUrl,
                    'slug' => $item->product ? $item->product->slug : null,
                ];
            });

            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'grand_total' => $order->grand_total,
                'snap_token' => $order->snap_token,
                'created_at' => $order->created_at->format('d M Y, H:i'),
                'items' => $formattedItems,
                'shipment' => $order->shipment ? [
                    'tracking_number' => $order->shipment->tracking_number,
                    'courier' => $order->shipment->courier,
                    'status' => $order->shipment->status,
                ] : null,
            ];
        });

        return Inertia::render('orders', [
            'orders' => $formattedOrders,
        ]);
    }
}
