<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = auth()->user()
            ->orders()
            ->latest()
            ->paginate(10);

        return view('front.orders.index', compact('orders'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $this->ensureOwner($order);

        $order->load('items.product', 'address');

        return view('front.orders.show', compact('order'));
    }

    public function success(Order $order)
    {
        $this->ensureOwner($order);

        return view('front.orders.success', compact('order'));
    }

    private function ensureOwner(Order $order): void
    {
        abort_unless((int) $order->user_id === auth()->id(), 403);
    }
}
