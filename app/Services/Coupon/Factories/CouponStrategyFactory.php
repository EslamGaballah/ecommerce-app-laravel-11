<?php

namespace App\Services\Coupon\Factories;

use App\Models\Coupon;
use App\Services\Coupon\Strategies\FixedDiscountStrategy;
use App\Services\Coupon\Strategies\PercentDiscountStrategy;
use App\Services\Coupon\Strategies\CouponStrategyInterface;
use App\Services\Coupon\Strategies\FreeShippingStrategy;

class CouponStrategyFactory
{
    public static function make(Coupon $coupon): CouponStrategyInterface
    {
        return match ($coupon->type) {
            'fixed' => new FixedDiscountStrategy(),
            'percent' => new PercentDiscountStrategy(),
            'free_shipping' => new FreeShippingStrategy(),
            default => throw new \Exception('Invalid coupon type'),
        };
    }
}