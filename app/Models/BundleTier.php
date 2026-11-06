<?php

namespace App\Models;

use App\Helpers\Currency;
use Illuminate\Database\Eloquent\Model;

class BundleTier extends Model
{
    public const PERCENT = 'percent';

    public const FIXED = 'fixed';

    public $timestamps = false;

    protected $fillable = ['min_quantity', 'discount_type', 'discount_value'];

    protected $casts = ['min_quantity' => 'integer', 'discount_value' => 'float'];

    /** Never more than the items cost. */
    public function discountOn(float $subtotal): float
    {
        $discount = $this->discount_type === self::PERCENT
            ? $subtotal * $this->discount_value / 100
            : $this->discount_value;

        return round(min($discount, $subtotal), 2);
    }

    /** "15%" or "$50.00". */
    public function getLabelAttribute(): string
    {
        return $this->discount_type === self::PERCENT
            ? rtrim(rtrim(number_format($this->discount_value, 2, '.', ''), '0'), '.').'%'
            : Currency::format($this->discount_value);
    }
}
