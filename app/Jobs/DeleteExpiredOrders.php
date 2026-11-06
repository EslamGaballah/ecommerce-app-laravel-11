<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\Order\OrderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class DeleteExpiredOrders implements ShouldQueue
{
    use Queueable;

    /**
     * Delete pending orders older than 30 days and return their items to stock.
     */
    public function handle(OrderService $orderService): void
    {
        Order::whereDate('created_at', '<', now()->subDays(30))
            ->where('status', 'pending')
            ->chunkById(100, function ($orders) use ($orderService) {
                foreach ($orders as $order) {
                    DB::transaction(function () use ($order, $orderService) {
                        $orderService->restoreStock($order);
                        $order->delete();
                    });
                }
            });
    }
}
