<?php

namespace App\Services\Order;

use App\Events\OrderCreated;
use App\Models\Governorate;
use App\Services\Coupon\CouponService;
use Illuminate\Support\Facades\DB;

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
    public function prepareCheckoutData($governorateId = null,  $code = null)
    {

        if ($this->cart->isEmpty()) {
            throw new \Exception('Cart is empty');
        }

        $items = $this->cart->items();
        $cartTotal = $this->cart->total();

        $tax = 20;

        $shipping = 0;

        if ($governorateId) {
            $governorate = Governorate::find($governorateId);
            $shipping = $governorate?->shipping_price ?? 0;
        }

        if ($code) {
            $coupon = $this->couponService->apply($code); 
        } else {
            $coupon = $this->couponService->getActiveCoupon();
        }

        $discount = $this->couponService->calculateDiscount($cartTotal, $coupon);

//         dd([
//     'code' => $code,
//     'coupon' => $coupon,
//     'discount' => $discount
// ]);
        $shipping = $this->couponService->applyShipping($shipping, $coupon);

        $totals = $this->calculateTotals($cartTotal, $shipping, $tax, $discount);

        return [
            'items' => $items,
            'cartTotal' => $cartTotal,
            'totals' => $totals,
        ];
    }

    // --------------------------
    // checkout
    // --------------------------
    public function checkout($data)
    {
        if ($this->cart->isEmpty()) {
            throw new \Exception('Cart is empty');
        }

        $items = $this->cart->items();
        $cartTotal = $this->cart->total();

        $coupon = $this->couponService->getActiveCoupon();

        $tax = 20;

        $governorate = Governorate::findOrFail($data['governorate_id']);
        $shipping = $governorate->shipping_price ?? 0;

        $discount = $this->couponService->calculateDiscount($cartTotal, $coupon);
        $shipping = $this->couponService->applyShipping($shipping, $coupon);

        $totals = $this->calculateTotals($cartTotal, $shipping, $tax, $discount);


        return $this->transaction->run(function () use (
            $items, $totals, $shipping, $tax, $coupon, $data
        ) {

            $order = $this->orderService->createOrder($totals, $shipping, $tax, $coupon);

            $this->orderService->createOrderItems($order, $items);

            $order->address()->create($data);

            $this->cart->empty();

            $this->couponService->finalize();

            DB::afterCommit(function () use ($order) {
                event(new OrderCreated($order));
            });

            return $order;
        });
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