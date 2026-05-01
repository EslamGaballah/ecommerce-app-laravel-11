<?php

namespace App\Services\Coupon;

use App\Models\Coupon;
use App\Services\Coupon\Factories\CouponStrategyFactory;

class CouponService
{
    public function getActiveCoupon()
    {
        return Coupon::find(session('coupon_id'));
    }

    public function applyShipping(float $shipping, $coupon = null): float
    {
        $coupon = $coupon ?? $this->getActiveCoupon();

        if ($coupon && $coupon->type === 'free_shipping') {
            return 0;
        }

        return $shipping;
    }

    public function apply(string $code)
    {
        $coupon = Coupon::where('code', trim($code))->first();

        if (!$coupon) {
            throw new \Exception('Invalid coupon code');
        }

        session(['coupon_id' => $coupon->id]);

        return $coupon;
    }

     public function calculateDiscount(float $cartTotal, $coupon = null): float
    {
        $coupon = $coupon ?? $this->getActiveCoupon();

        if (!$coupon) return 0;

        if ($coupon->type === 'free_shipping') {
            return 0;
        }

        $strategy = CouponStrategyFactory::make($coupon);

        return $strategy->calculate($cartTotal, $coupon->value);
    }

    public function finalize()
    {
        $coupon = $this->getActiveCoupon();

        if ($coupon) {
            $coupon->increment('used_count');
            session()->forget('coupon_id');
        }
    }
}