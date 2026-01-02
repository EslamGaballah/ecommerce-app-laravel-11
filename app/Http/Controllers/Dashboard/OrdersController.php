<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $orders = Order::with(['user', 'address'])
            ->filter($request->query())
            ->latest()
            ->paginate();

        return view('dashboard.orders.index', compact('orders'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $order->load('items.product', 'address');

        return view('dashboard.orders.show', compact('order'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,delivering,completed,cancelled,refunded',
        ]);

        DB::transaction(function () use ($request, $order) {

        $oldStatus = $order->status;
        $newStatus = $request->status;

        
        $order->update([
            'status' => $newStatus
        ]);

        // if order cancelled or refunded
        if (
            in_array($newStatus, ['cancelled', 'refunded']) &&
            !in_array($oldStatus, ['cancelled', 'refunded'])
        ) {
            $order->load('items.product');

            foreach ($order->items as $item) {
                $item->product->increment('quantity', $item->quantity);
            }
        }
    });

        return redirect()->back()->with('success', 'Order Status Updated ');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // it should be soft delete
    }
}
