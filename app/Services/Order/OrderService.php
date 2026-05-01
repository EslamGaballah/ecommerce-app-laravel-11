<?php

namespace App\Services\Order;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;

class OrderService
{
    public function createOrder($totals, $shipping, $tax, $coupon = null)
    {
        return Order::create([
            'user_id' => Auth::id(),
            'status' => OrderStatus::PENDING,
            'coupon_id' => $coupon?->id,
            'discount' => $totals['discount'],
            'shipping' => $shipping,
            'tax' => $tax,
            'total' => $totals['total'],
        ]);
    }

    public function createOrderItems($order, $items)
    {
        foreach ($items as $item) {

            $stock = $item->variation?->stock ?? $item->product->stock;

            if ($stock < $item->quantity) {
                throw new \Exception("Product {$item->product->name} is out of stock.");
            }

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

            // تقليل المخزون
            if ($item->variation_id) {
                $item->variation->decrement('stock', $item->quantity);
            } else {
                $item->product->decrement('stock', $item->quantity);
            }
        }
    }
}