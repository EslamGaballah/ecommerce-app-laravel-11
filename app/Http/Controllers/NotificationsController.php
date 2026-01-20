<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function index()
    {
        $notifications =  auth()->user()
            ->notifications()
            ->latest()
            ->paginate();

        return view('notifications.index', compact('notifications'));
    }

    public function show(Order $order)
    {
        auth()->user()
            ->unreadNotifications()
            ->where('data.order_id', $order->id)
            ->markAsRead();

        return view('dashboard.orders.show', compact('order'));
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return back();
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back();
    }

}
