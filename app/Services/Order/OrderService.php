<?php

namespace App\Services\Order;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Exceptions\CheckoutException;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Support\Facades\Auth;

class OrderService
{
    public function createOrder($totals, $shipping, $tax, PaymentMethod $paymentMethod, $coupon = null)
    {
        return Order::create([
            'user_id' => Auth::id(),
            'status' => OrderStatus::PENDING,
            'payment_method' => $paymentMethod,
            'payment_status' => PaymentStatus::PENDING,
            'coupon_id' => $coupon?->id,
            'discount' => $totals['discount'],
            'bundle_discount' => $totals['bundle_discount'] ?? 0,
            'shipping' => $shipping,
            'tax' => $tax,
            'total' => $totals['total'],
        ]);
    }

    public function createOrderItems($order, $items)
    {
        foreach ($items as $item) {
            $price = $item->variation?->price ?? $item->product->price;

            $options = null;

            if ($item->variation && $item->variation->values->count()) {
                $options = $item->variation->values->mapWithKeys(function ($v) {
                    return [$v->attribute->name => $v->value];
                });
            }

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'variation_id' => $item->variation_id,
                'product_name' => $item->product->name,
                'price' => $price,
                'quantity' => $item->quantity,
                'options' => $options,
            ]);

            // The stock condition is part of the update, so simultaneous orders cannot oversell.
            $stockable = $item->variation_id
                ? ProductVariation::whereKey($item->variation_id)
                : Product::whereKey($item->product_id);

            if (! $stockable->where('stock', '>=', $item->quantity)->decrement('stock', $item->quantity)) {
                throw new CheckoutException(__('app.item_out_of_stock', ['name' => $item->product->name]));
            }
        }
    }

    /**
     * Put an order's items back into stock. Call it inside a transaction.
     */
    public function restoreStock(Order $order): void
    {
        foreach ($order->items()->get() as $item) {
            if ($item->variation_id) {
                ProductVariation::whereKey($item->variation_id)->increment('stock', $item->quantity);
            } elseif ($item->product_id) {
                Product::withTrashed()->whereKey($item->product_id)->increment('stock', $item->quantity);
            }
        }
    }
}
