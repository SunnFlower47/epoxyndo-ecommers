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
            }

            // Restore stock when status changes to 'cancelled' 
            // ONLY if it was previously paid (because stock is only deducted when paid)
            if ($order->wasChanged('status') && $order->status === self::STATUS_CANCELLED) {
                if ($order->payment_status === self::PAYMENT_PAID || $order->getOriginal('payment_status') === self::PAYMENT_PAID) {
                    foreach ($order->items as $item) {
                        if ($item->variant_id && $item->variant) {
                            $item->variant->increment('stock', $item->quantity);
                        } else if ($item->product) {
                            $item->product->increment('stock', $item->quantity);
                        }
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
