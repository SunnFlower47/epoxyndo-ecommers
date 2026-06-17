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

        // Optional: Check if user actually bought the product
        $hasBought = \App\Models\OrderItem::where('product_id', $request->product_id)
            ->whereHas('order', function ($q) {
                $q->where('user_id', Auth::id())
                  ->where('status', 'completed');
            })->exists();

        if (!$hasBought) {
            return back()->with('error', 'Anda hanya dapat memberikan ulasan untuk produk yang telah Anda beli dan pesanannya telah selesai.');
        }

        // Check if user already reviewed
        $existingReview = Review::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->first();

        if ($existingReview) {
            return back()->with('error', 'Anda sudah memberikan ulasan untuk produk ini.');
        }

        Review::create([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'is_approved' => true, // Auto-approve for now, or you can set to false for moderation
        ]);

        return back()->with('success', 'Terima kasih! Ulasan Anda telah berhasil dikirim.');
    }
}
