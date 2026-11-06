<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Order\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;

class OrdersController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Order::class, 'order');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $orders = Order::with(['user', 'updatedBy', 'address'])
            ->filter($request->query())
            ->latest()
            ->paginate(10);

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
    public function update(Request $request, Order $order, OrderService $orderService)
    {
        $request->validate([
            'status' => ['required', new Enum(OrderStatus::class)],
        ]);

        $newStatus = OrderStatus::from($request->status);

        $changed = DB::transaction(function () use ($order, $newStatus, $orderService) {
            // Lock the row so two requests cannot both restore the same stock.
            $current = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! $current->status->canTransitionTo($newStatus)) {
                return false;
            }

            $current->statusHistories()->create([
                'old_status' => $current->status->value,
                'new_status' => $newStatus->value,
                'updated_by' => auth()->id(),
            ]);

            if ($newStatus->isClosed()) {
                $orderService->restoreStock($current);
            }

            $current->status = $newStatus;

            if ($newStatus === OrderStatus::COMPLETED) {
                $current->payment_status = PaymentStatus::PAID;
            } elseif ($newStatus === OrderStatus::REFUNDED && $current->payment_status === PaymentStatus::PAID) {
                $current->payment_status = PaymentStatus::REFUNDED;
            }

            $current->save();

            return true;
        });

        if (! $changed) {
            $message = __('app.order_status_not_allowed');

            return $request->expectsJson()
                ? response()->json(['message' => $message], 422)
                : back()->with('error', $message);
        }

        $order->refresh();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'status' => $order->status->value,
                'label' => $order->status->label(),
                'color' => $order->status->color(),
                'updated_by' => auth()->user()->name ?? 'N/A',
                'updated_at' => $order->updated_at->format('Y-m-d H:i'),
            ]);
        }

        return back()->with('success', 'Order Status Updated successfully');
    }

    /**
     * Only closed orders can be deleted, so stock and sales records stay correct.
     */
    public function destroy(Order $order)
    {
        if (! $order->status->isClosed()) {
            return back()->with('error', __('app.order_delete_not_allowed'));
        }

        $order->delete();

        return back()->with('success', __('app.order_deleted'));
    }
}
