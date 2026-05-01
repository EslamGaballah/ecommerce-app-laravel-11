<?php

namespace App\Services\Coupon\Strategies;

class PercentDiscountStrategy implements CouponStrategyInterface
{
    public function calculate(float $cartTotal, float $value): float
    {
        $percentage = min(max($value, 0), 100);

        return ($cartTotal * $percentage) / 100;
    }
}