<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated 
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

     public $order;
    /**
     * Create a new event instance.
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    // public function broadcastOn()
    // {
    //     return [
    //         new PrivateChannel('OrderCreated'),
    //     ];
    //     //   return [
    //     //     new Channel('my-channel'),
    //     // ];

    // }

    /**
     * The event's broadcast name.
     */
    // public function broadcastAs()
    // {

    //     // return 'order.created';
    //     return 'OrderCreated';
    //     //  return 'my-event';
    // }
}
