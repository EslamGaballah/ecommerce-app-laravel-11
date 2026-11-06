<?php

namespace App\Services\Coupon;

use App\Exceptions\CheckoutException;
use App\Models\Coupon;
use App\Services\Coupon\Factories\CouponStrategyFactory;

class CouponService
{
    public function hasAppliedCoupon(): bool
    {
        return session()->has('coupon_id');
    }

    /**
     * The coupon applied in this session, if it is still valid for this total.
     */
    public function getActiveCoupon(float $cartTotal): ?Coupon
    {
        $coupon = Coupon::find(session('coupon_id'));

        return $coupon?->isValid($cartTotal) ? $coupon : null;
    }

    public function apply(string $code, float $cartTotal): Coupon
    {
        $coupon = Coupon::where('code', trim($code))->first();

        if (! $coupon || ! $coupon->isValid($cartTotal)) {
            $this->clear();

            throw new CheckoutException(__('app.invalid_coupon'));
        }

        session(['coupon_id' => $coupon->id]);

        return $coupon;
    }

    public function applyShipping(float $shipping, ?Coupon $coupon): float
    {
        return $coupon?->type === 'free_shipping' ? 0 : $shipping;
    }

    public function calculateDiscount(float $cartTotal, ?Coupon $coupon): float
    {
        if (! $coupon) {
            return 0;
        }

        return CouponStrategyFactory::make($coupon)->calculate($cartTotal, $coupon->value);
    }

    /**
     * Count one use. The limit is part of the update, so simultaneous orders cannot exceed it.
     */
    public function redeem(Coupon $coupon): void
    {
        $redeemed = Coupon::whereKey($coupon->id)
            ->where(fn ($query) => $query->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'))
            ->increment('used_count');

        if (! $redeemed) {
            throw new CheckoutException(__('app.coupon_no_longer_valid'));
        }
    }

    public function clear(): void
    {
        session()->forget('coupon_id');
    }
}
