<?php

namespace App\Services\Order;

use App\Enums\PaymentMethod;
use App\Events\OrderCreated;
use App\Exceptions\CheckoutException;
use App\Helpers\Currency;
use App\Models\Governorate;
use App\Models\Setting;
use App\Services\Bundle\BundleService;
use App\Services\Coupon\CouponService;

class CheckoutService
{
    public function __construct(
        protected CartService $cart,
        protected OrderService $orderService,
        protected CouponService $couponService,
        protected TransactionService $transaction,
        protected BundleService $bundles
    ) {}

    // --------------------------
    // prepare date
    // --------------------------
    public function prepareCheckoutData($governorateId = null, $code = null)
    {
        $this->ensureCartHasItems();

        $items = $this->cart->items();
        $cartTotal = $this->cart->total();

        $tax = (float) Setting::get('tax');

        $shipping = 0;

        if ($governorateId) {
            $governorate = Governorate::active()->find($governorateId);
            $shipping = $governorate?->shipping_price ?? 0;
        }

                // A coupon applies to what is left after the bundle discount.
        $bundleDiscount = $this->bundles->calculate($items)['discount'];
        $afterBundles = max($cartTotal - $bundleDiscount, 0);

        $coupon = $code
            ? $this->couponService->apply($code, $afterBundles)
            : $this->couponService->getActiveCoupon($afterBundles);

        $discount = $bundleDiscount + $this->couponService->calculateDiscount($afterBundles, $coupon);
                $shipping = $this->couponService->applyShipping($shipping, $coupon);
        $shipping = $this->applyFreeShipping($shipping, $cartTotal, $discount);

        return [
            'items' => $items,
            'cartTotal' => $cartTotal,
            'totals' => $this->calculateTotals($cartTotal, $shipping, $tax, $discount, $bundleDiscount),
        ];
    }

    /**
     * What this cart may be paid with. Measured on the products before any discount or shipping,
     * so applying a coupon or choosing a governorate never changes the choices on the page.
     *
     * @return array{methods: array<int, PaymentMethod>, cashBlockedReason: ?string}
     */
    public function paymentOptions(): array
    {
        $methods = PaymentMethod::enabled();
        $reason = in_array(PaymentMethod::CASH, $methods, true) ? $this->cashLimitReason() : null;

        return [
            'methods' => $reason
                ? array_values(array_filter($methods, fn ($method) => $method !== PaymentMethod::CASH))
                : $methods,
            'cashBlockedReason' => $reason,
        ];
    }

    private function cashLimitReason(): ?string
    {
        $maxItems = (int) Setting::get('cod_max_items');
        $maxAmount = (float) Setting::get('cod_max_amount');

        if ($maxItems > 0 && $this->cart->items()->sum('quantity') > $maxItems) {
            return __('app.cod_limit_items', ['count' => $maxItems]);
        }

        if ($maxAmount > 0 && $this->cart->total() > $maxAmount) {
            return __('app.cod_limit_amount', ['amount' => Currency::format($maxAmount)]);
        }

        return null;
    }

    // --------------------------
    // checkout
    // --------------------------
    public function checkout($data)
    {
        $this->ensureCartHasItems();

        // The page hides a method the cart can't use, but a request can still ask for it.
        $options = $this->paymentOptions();

        if (! in_array(PaymentMethod::from($data['payment_method']), $options['methods'], true)) {
            throw new CheckoutException($options['cashBlockedReason'] ?? __('app.payment_method_unavailable'));
        }

        $items = $this->cart->items();
        $cartTotal = $this->cart->total();

        // Never silently charge more than the customer saw on the checkout page.
        $hadCoupon = $this->couponService->hasAppliedCoupon();
                $bundleDiscount = $this->bundles->calculate($items)['discount'];
        $afterBundles = max($cartTotal - $bundleDiscount, 0);
        $coupon = $this->couponService->getActiveCoupon($afterBundles);

        if ($hadCoupon && ! $coupon) {
            throw new CheckoutException(__('app.coupon_no_longer_valid'));
        }

        $tax = (float) Setting::get('tax');

        $governorate = Governorate::active()->findOrFail($data['governorate_id']);

                $discount = $bundleDiscount + $this->couponService->calculateDiscount($afterBundles, $coupon);
        $shipping = $this->couponService->applyShipping($governorate->shipping_price ?? 0, $coupon);
        $shipping = $this->applyFreeShipping($shipping, $cartTotal, $discount);

        $totals = $this->calculateTotals($cartTotal, $shipping, $tax, $discount, $bundleDiscount);

        $order = $this->transaction->run(function () use ($items, $totals, $shipping, $tax, $coupon, $data) {
            $order = $this->orderService->createOrder($totals, $shipping, $tax, PaymentMethod::from($data['payment_method']), $coupon);

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

        $minimum = (float) Setting::get('min_order_amount');

        if ($minimum > 0 && $this->cart->total() < $minimum) {
            throw new CheckoutException(__('app.min_order_not_met', ['amount' => Currency::format($minimum)]));
        }
    }

    /**
     * Measured after the discount, so a coupon can't be used to reach free shipping for less.
     */
    private function applyFreeShipping($shipping, $cartTotal, $discount)
    {
        $minimum = (float) Setting::get('free_shipping_min');

        return $minimum > 0 && $cartTotal - $discount >= $minimum ? 0 : $shipping;
    }


        private function calculateTotals($cartTotal, $shipping, $tax, $discount, $bundleDiscount = 0)
    {
        $subtotal = max($cartTotal - $discount, 0);

        return [
            'original' => $cartTotal,
            'discount' => $discount,
            'bundle_discount' => $bundleDiscount,
            'shipping' => $shipping,
            'tax' => $tax,
            'total' => $subtotal + $shipping + $tax,
        ];
    }
}
