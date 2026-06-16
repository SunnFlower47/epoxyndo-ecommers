<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    protected $fillable = [
        'order_id',
        'courier_name',
        'courier_service',
        'tracking_number',
        'status',
        'biteship_order_id',
        'biteship_tracking_id',
        'shipping_address',
    ];

    protected function casts(): array
    {
        return [
            'shipping_address' => 'array',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
