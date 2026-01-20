<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\User;
use App\Notifications\OrderCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

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
        $user->notify(
            new OrderCreatedNotification($event->order)
        );
        
        // $user->notifyNow(new OrderCreatedNotification($event->order));  // to avoid queue

        $admins = User::whereHas('roles', function ($q) {
            $q->where('name', 'admin');
        })->get();

         Notification::send($admins, new OrderCreatedNotification($event->order)); // if there is many users

    }
}
