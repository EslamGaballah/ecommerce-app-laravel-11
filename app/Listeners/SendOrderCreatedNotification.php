<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Notifications\OrderCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;

class SendOrderCreatedNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
         $user = $event->order->user()->first();
        //  dd($user);
        $user->notify(new OrderCreatedNotification($event->order));
        // $user->notifyNow(new OrderCreatedNotification($event->order));  // to avoid queue

         // Notification::send($users, new OrderCreatedNotification($order)); // if there is many users
    }
}
