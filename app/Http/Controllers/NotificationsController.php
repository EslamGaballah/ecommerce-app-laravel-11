<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $onlyUnread = $request->query('filter') === 'unread';

        $notifications = ($onlyUnread ? $user->unreadNotifications() : $user->notifications())
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view($request->routeIs('dashboard.*') ? 'dashboard.notifications.index' : 'notifications.index', [
            'notifications' => $notifications,
            'onlyUnread' => $onlyUnread,
            'unreadCount' => $user->unreadNotifications()->count(),
            'totalCount' => $user->notifications()->count(),
        ]);
    }

    /** Marks it read and goes to whatever it is about. */
    public function open(Request $request, string $id): RedirectResponse
    {
        $notification = $request->user()->notifications()->whereKey($id)->firstOrFail();

        $notification->markAsRead();

        $orderId = $notification->data['order_id'] ?? null;

        if ($orderId) {
            return $request->user()->can('access-dashboard')
                ? redirect()->route('dashboard.orders.show', $orderId)
                : redirect()->route('front.orders.show', $orderId);
        }

        return back();
    }

    public function markAsRead(Request $request, string $id): RedirectResponse
    {
        $request->user()->notifications()->whereKey($id)->firstOrFail()->markAsRead();

        return back();
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        $request->user()->notifications()->whereKey($id)->firstOrFail()->delete();

        return back()->with('success', __('app.deleted_successfully'));
    }

    public function destroyRead(Request $request): RedirectResponse
    {
        $request->user()->readNotifications()->delete();

        return back()->with('success', __('app.deleted_successfully'));
    }

    public function show(Order $order)
    {
        auth()->user()
            ->unreadNotifications()
            ->where('data->order_id', $order->id)
            ->update(['read_at' => now()]);

        return view('dashboard.orders.show', compact('order'));
    }
}
