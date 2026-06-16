<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * Display a listing of orders (Order History).
     */
    public function index(Request $request): Response
    {
        $orders = Order::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Shop/OrderHistory', [
            'orders' => $orders,
        ]);
    }

    /**
     * Display order details.
     */
    public function show(int $id): Response
    {
        $order = Order::where('user_id', auth()->id())
            ->with(['items.product', 'shipment'])
            ->findOrFail($id);

        return Inertia::render('Shop/OrderDetail', [
            'order' => $order,
        ]);
    }
}
