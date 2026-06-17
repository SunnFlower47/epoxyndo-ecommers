<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasTranslations, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'variant_label',
        'slug',
        'description',
        'sku',
        'price',
        'discount_value',
        'discount_type',
        'discount_start',
        'discount_end',
        'stock',
        'weight',
        'unit_id',
        'moq',
        'is_bulky',
        'is_active',
        'is_preorder',
        'preorder_days',
    ];

    public array $translatable = ['name', 'description'];

    protected function casts(): array
    {
        return [
            'price'          => 'decimal:2',
            'discount_value' => 'decimal:2',
            'discount_start' => 'datetime',
            'discount_end'   => 'datetime',
            'stock'          => 'integer',
            'weight'         => 'decimal:2',
            'moq'            => 'integer',
            'is_bulky'       => 'boolean',
            'is_active'      => 'boolean',
            'is_preorder'    => 'boolean',
            'preorder_days'  => 'integer',
        ];
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    /**
     * Apakah produk sedang dalam periode flash sale aktif.
     */
    public function getIsFlashSaleAttribute(): bool
    {
        if (! $this->discount_start || ! $this->discount_end) {
            return false;
        }

        $now = Carbon::now();

        return $now->between($this->discount_start, $this->discount_end);
    }

    /**
     * Apakah produk memiliki diskon (reguler atau flash sale aktif).
     */
    public function getHasDiscountAttribute(): bool
    {
        if (! $this->discount_value) {
            return false;
        }

        // Diskon reguler (tanpa batas waktu)
        if (! $this->discount_start && ! $this->discount_end) {
            return true;
        }

        // Flash sale — hanya aktif dalam periode
        return $this->is_flash_sale;
    }

    /**
     * Harga akhir setelah diskon (jika ada).
     */
    public function getFinalPriceAttribute(): float
    {
        if (! $this->has_discount) {
            return (float) $this->price;
        }

        if ($this->discount_type === 'percentage') {
            return (float) $this->price * (1 - ($this->discount_value / 100));
        }

        return max(0, (float) $this->price - (float) $this->discount_value);
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function activeVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->where('is_active', true)->orderBy('sort_order');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function stockMutations(): HasMany
    {
        return $this->hasMany(StockMutation::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }
}
