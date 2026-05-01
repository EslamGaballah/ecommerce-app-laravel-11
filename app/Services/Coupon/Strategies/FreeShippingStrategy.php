<?php

namespace App\Services\Coupon\Strategies;

class FreeShippingStrategy implements CouponStrategyInterface
{
    /**
     * Free shipping means discount = shipping cost
     * so we return shipping value as discount equivalent
     */
    public function calculate(float $cartTotal, float $value): float
    {
        // هنا value ممكن تستخدمه لو حابب تحدد max shipping coverage لاحقًا
        // return $value;
        return 0;
    }
}