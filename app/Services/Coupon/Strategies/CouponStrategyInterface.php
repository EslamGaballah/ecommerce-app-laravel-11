<?php

namespace App\Services\Coupon\Strategies;

interface CouponStrategyInterface
{
    public function calculate(float $cartTotal, float $value): float;
}