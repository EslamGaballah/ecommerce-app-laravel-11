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
        // $orders = Order::where('user_id', Auth::id())
        //     ->latest()
        //     ->paginate();

        return view('front.orders.index', compact('orders'));
    
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
        abort(403);
    }

    $order->load('items.product', 'address');

    return view('front.orders.show', compact('order'));
    }

    public function success(Order $order)
    {
        return view('front.orders.success', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
