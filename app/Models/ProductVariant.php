<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasUlids;

    protected $fillable = [
        'product_id',
        'label',
        'sku',
        'price',
        'stock',
        'weight',
        'is_bulky',
        'sort_order',
        'is_active',
    ];

    protected $appends = ['final_price'];

    protected function casts(): array
    {
        return [
            'price'      => 'decimal:2',
            'stock'      => 'integer',
            'weight'     => 'decimal:2',
            'is_bulky'   => 'boolean',
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getFinalPriceAttribute()
    {
        $product = $this->product;
        
        if (!$product || !$product->has_discount || empty($product->discount_value)) {
            return $this->price;
        }

        if ($product->discount_type === 'percentage') {
            return (float) $this->price * (1 - ($product->discount_value / 100));
        }

        return max(0, (float) $this->price - (float) $product->discount_value);
    }
}
