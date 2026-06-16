<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'min_purchase',
        'valid_from',
        'valid_until',
        'max_uses',
        'used_count',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'min_purchase'   => 'decimal:2',
            'valid_from'     => 'datetime',
            'valid_until'    => 'datetime',
            'max_uses'       => 'integer',
            'used_count'     => 'integer',
            'is_active'      => 'boolean',
        ];
    }

    /**
     * Apakah kupon masih valid saat ini.
     */
    public function getIsValidAttribute(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->max_uses && $this->used_count >= $this->max_uses) {
            return false;
        }

        $now = now();

        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        return true;
    }
}
