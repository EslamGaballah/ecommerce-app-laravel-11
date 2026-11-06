<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;

class OrderStatusChangedNotification extends Notification
{
    public function __construct(public Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Saved as data, not as a sentence, so it reads in the customer's language when they open it.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'kind' => 'order_status',
            'order_id' => $this->order->id,
            'number' => $this->order->number,
            'status' => $this->order->status->value,
        ];
    }
}
