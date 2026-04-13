<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_amount',
        'usage_limit',
        'used_count',
        'expires_at',
        'is_active',
    ];
    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function isValid($total)
    {
        if (!$this->is_active) return false;

        if ($this->expires_at && now()->gt($this->expires_at)) return false;

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) return false;

        if ($this->min_order_amount && $total < $this->min_order_amount) return false;

        return true;
    }}
