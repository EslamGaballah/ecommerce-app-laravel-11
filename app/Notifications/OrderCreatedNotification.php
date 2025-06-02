<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCreatedNotification extends Notification
{
    use Queueable;

    protected $order;
    /**
     * Create a new notification instance.
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array // $notifiable = model like user that will receive notification
    {
        return [
            // 'mail',
             'database', 'broadcast'];

        $channels = ['database'];  // database (default)

        // if ($notifiable->notification_preferences['order_created']['sms'] ?? false) {
        //     $channels[] = 'vonage';
        // }
        // if ($notifiable->notification_preferences['order_created']['mail'] ?? false) {
        //     $channels[] = 'mail';
        // }
        if ($notifiable->notification_preferences['order_created']['broadcast'] ?? false) {
            $channels[] = 'broadcast';
        }
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    // public function toMail(object $notifiable): MailMessage
    // {
    //     return (new MailMessage)
    //                 ->subject ('New Order #' . $this->order->number)
    //                 // ->from('notification@ajyal-store.ps', 'AJYAL Store') // default from env data
    //                 ->greeting("Hi {$notifiable->name},")
    //                 ->line('A new order has been created by ')
    //                 ->line('bla bla bla bla ')
    //                 ->action('view order', url('/'))
    //                 ->line('Thank you for using our application!');
    // }

    // public function toDatabase(object $notifiable)
    // {
    //     return [
    //          'body' => "A new order (#{$this->order->number}) created by ((bla bla )).",
    //         'icon' => 'fas fa-file',
    //         'url' => url('/dashboard'),
    //         'order_id' => $this->order->id,
    //     ];
                    
    // }
     public function toBroadcast($notifiable)
    {

        //object
        return new BroadcastMessage([
            'body' => "A new order (#{$this->order->number}) created by",
            'icon' => 'fas fa-file',
            'url' => url('/dashboard'),
            'order_id' => $this->order->id,
        ]);

        // array
        //   return[
        //     'body' => "A new order (#{$this->order->number}) created by",
        //     'icon' => 'fas fa-file',
        //     'url' => url('/dashboard'),
        //     'order_id' => $this->order->id,
        // ];
    }




    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
         return [
            // 'message' => 'تم إرسال إشعار جديد',
            // 'user_id' => $notifiable->id,
        ];
    }
}
