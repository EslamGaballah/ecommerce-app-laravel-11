<?php

namespace App\Services\Coupon\Strategies;

class FixedDiscountStrategy implements CouponStrategyInterface
{
    public function calculate(float $cartTotal, float $value): float
    {
        $discount = min($value, $cartTotal);

        return max($discount, 0);
    }
}
