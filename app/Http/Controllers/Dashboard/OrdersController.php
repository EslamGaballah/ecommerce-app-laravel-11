<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;

class OrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $orders = Order::with(['user','updatedBy', 'address'])
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
            'status' => ['required', new Enum(OrderStatus::class)],
        ]);

        if ($order->status === $request->status) {
                return response()->json([
                    'message' => 'Same status'
                ], 422);
            }

        DB::transaction(function () use ($request, $order) {

            $oldStatus = $order->status->value;
            $newStatus = $request->status->value;

            // create order history status
            $order->statusHistories()->create([
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'updated_by' => auth()->id(),
        ]);
            
            // update status
            $order->update([
                'status' => $newStatus,
                'updated_by' => auth()->id(),
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

                return response()->json([
                    'success' => true,
                    'status'  =>  $order->status->value,
                    'label'  => $order->status->label(),
                    'color'  => $order->status->color(),
                    'updated_by' => auth()->user()->name,
                    'updated_at' => $order->updated_at->format('Y-m-d H:i'),
                ]);
            

        

        return back()->with('success', 'Order Status Updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // it should be soft delete
    }
}
