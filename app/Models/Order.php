<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'order_number',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'shipping_cost',
        'grand_total',
        'payment_method',
        'payment_status',
        'snap_token',
        'notes',
        'shipping_address',
        'courier',
        'courier_service',
        'coupon_id',
    ];

    // Status constants
    const STATUS_PENDING    = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED    = 'shipped';
    const STATUS_COMPLETED  = 'completed';
    const STATUS_CANCELLED  = 'cancelled';

    const PAYMENT_UNPAID = 'unpaid';
    const PAYMENT_PAID   = 'paid';
    const PAYMENT_FAILED = 'failed';

    protected function casts(): array
    {
        return [
            'subtotal'         => 'decimal:2',
            'tax_amount'       => 'decimal:2',
            'discount_amount'  => 'decimal:2',
            'shipping_cost'    => 'decimal:2',
            'grand_total'      => 'decimal:2',
            'shipping_address' => 'array',
        ];
    }

    // ─── Lifecycle Hooks ──────────────────────────────────────────────────────
    protected static function booted()
    {
        static::updated(function (Order $order) {
            // Deduct stock when payment_status changes to 'paid'
            if ($order->wasChanged('payment_status') && $order->payment_status === self::PAYMENT_PAID) {
                foreach ($order->items as $item) {
                    if ($item->variant_id && $item->variant) {
                        $item->variant->decrement('stock', $item->quantity);
                    } else if ($item->product) {
                        $item->product->decrement('stock', $item->quantity);
                    }
                }

                // Send Paid Email
                try {
                    \Illuminate\Support\Facades\Mail::to($order->customer_email ?? $order->user?->email)
                        ->send(new \App\Mail\OrderPaidMail($order));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to send OrderPaidMail: ' . $e->getMessage());
                }
            }

            // Restore stock when status changes to 'cancelled' 
            // ONLY if it was previously paid (because stock is only deducted when paid)
            if ($order->wasChanged('status')) {
                if ($order->status === self::STATUS_CANCELLED) {
                    if ($order->payment_status === self::PAYMENT_PAID || $order->getOriginal('payment_status') === self::PAYMENT_PAID) {
                        foreach ($order->items as $item) {
                            if ($item->variant_id && $item->variant) {
                                $item->variant->increment('stock', $item->quantity);
                            } else if ($item->product) {
                                $item->product->increment('stock', $item->quantity);
                            }
                        }
                    }
                    
                    // Restore coupon usage limit if a coupon was used
                    if ($order->coupon_id && $order->coupon) {
                        $order->coupon->decrement('used_count');
                    }
                    
                    try {
                        \Illuminate\Support\Facades\Mail::to($order->customer_email ?? $order->user?->email)
                            ->send(new \App\Mail\OrderCancelledMail($order));
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Failed to send OrderCancelledMail on status change: ' . $e->getMessage());
                    }
                } else if ($order->status === self::STATUS_SHIPPED) {
                    try {
                        \Illuminate\Support\Facades\Mail::to($order->customer_email ?? $order->user?->email)
                            ->send(new \App\Mail\OrderShippedMail($order, $order->shipment));
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Failed to send OrderShippedMail on status change: ' . $e->getMessage());
                    }
                } else if ($order->status === self::STATUS_COMPLETED) {
                    try {
                        \Illuminate\Support\Facades\Mail::to($order->customer_email ?? $order->user?->email)
                            ->send(new \App\Mail\OrderDeliveredMail($order, $order->shipment));
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Failed to send OrderDeliveredMail on status change: ' . $e->getMessage());
                    }
                }
            }
        });
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }
}
