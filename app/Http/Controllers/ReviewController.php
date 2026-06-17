<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Store a newly created review in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Check if user actually bought the product and count how many times
        $boughtCount = \App\Models\OrderItem::where('product_id', $request->product_id)
            ->whereHas('order', function ($q) {
                $q->where('user_id', Auth::id())
                  ->where('status', 'completed');
            })->count();

        if ($boughtCount === 0) {
            return response()->json(['message' => 'Anda hanya dapat memberikan ulasan untuk produk yang telah Anda beli dan pesanannya telah selesai.'], 403);
        }

        // Check how many reviews the user has already written for this product
        $reviewCount = Review::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->count();

        if ($reviewCount >= $boughtCount) {
            return response()->json(['message' => 'Anda sudah memberikan ulasan untuk pesanan ini.'], 400);
        }

        Review::create([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_approved' => true, // Auto-approve for now, or you can set to false for moderation
        ]);

        return response()->json(['message' => 'Terima kasih! Ulasan Anda telah berhasil dikirim.']);
    }
}
