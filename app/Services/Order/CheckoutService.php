<?php

namespace App\Services\Order;

use App\Events\OrderCreated;
use App\Exceptions\CheckoutException;
use App\Models\Governorate;
use App\Services\Coupon\CouponService;

class CheckoutService
{
    public function __construct(
        protected CartService $cart,
        protected OrderService $orderService,
        protected CouponService $couponService,
        protected TransactionService $transaction
    ) {}

    // --------------------------
    // prepare date
    // --------------------------
    public function prepareCheckoutData($governorateId = null, $code = null)
    {
        $this->ensureCartHasItems();

        $items = $this->cart->items();
        $cartTotal = $this->cart->total();

        $tax = config('shop.flat_tax');

        $shipping = 0;

        if ($governorateId) {
            $governorate = Governorate::active()->find($governorateId);
            $shipping = $governorate?->shipping_price ?? 0;
        }

        $coupon = $code
            ? $this->couponService->apply($code, $cartTotal)
            : $this->couponService->getActiveCoupon($cartTotal);

        $discount = $this->couponService->calculateDiscount($cartTotal, $coupon);
        $shipping = $this->couponService->applyShipping($shipping, $coupon);

        return [
            'items' => $items,
            'cartTotal' => $cartTotal,
            'totals' => $this->calculateTotals($cartTotal, $shipping, $tax, $discount),
        ];
    }

    // --------------------------
    // checkout
    // --------------------------
    public function checkout($data)
    {
        $this->ensureCartHasItems();

        $items = $this->cart->items();
        $cartTotal = $this->cart->total();

        // Never silently charge more than the customer saw on the checkout page.
        $hadCoupon = $this->couponService->hasAppliedCoupon();
        $coupon = $this->couponService->getActiveCoupon($cartTotal);

        if ($hadCoupon && ! $coupon) {
            throw new CheckoutException(__('app.coupon_no_longer_valid'));
        }

        $tax = config('shop.flat_tax');

        $governorate = Governorate::active()->findOrFail($data['governorate_id']);

        $discount = $this->couponService->calculateDiscount($cartTotal, $coupon);
        $shipping = $this->couponService->applyShipping($governorate->shipping_price ?? 0, $coupon);

        $totals = $this->calculateTotals($cartTotal, $shipping, $tax, $discount);

        $order = $this->transaction->run(function () use ($items, $totals, $shipping, $tax, $coupon, $data) {
            $order = $this->orderService->createOrder($totals, $shipping, $tax, $coupon);

            $this->orderService->createOrderItems($order, $items);

            $order->address()->create($data);

            if ($coupon) {
                $this->couponService->redeem($coupon);
            }

            $this->cart->empty();

            return $order;
        });

        $this->couponService->clear();

        // The order is saved, so a notification failure is logged instead of shown to the customer.
        rescue(fn () => event(new OrderCreated($order)));

        return $order;
    }

    private function ensureCartHasItems(): void
    {
        if ($this->cart->isEmpty()) {
            throw new CheckoutException(__('app.cart_is_empty'));
        }
    }

    private function calculateTotals($cartTotal, $shipping, $tax, $discount)
    {
        $subtotal = max($cartTotal - $discount, 0);

        return [
            'original' => $cartTotal,
            'discount' => $discount,
            'shipping' => $shipping,
            'tax' => $tax,
            'total' => $subtotal + $shipping + $tax,
        ];
    }
}
