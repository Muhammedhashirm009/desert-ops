<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductRequestReceived extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $dispatch;

    /**
     * Create a new notification instance.
     */
    public function __construct(\App\Models\Dispatch $dispatch)
    {
        $this->dispatch = $dispatch;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->dispatch->load('outlet');
        $itemCount = $this->dispatch->items()->count();
        return [
            'dispatch_id' => $this->dispatch->id,
            'dispatch_number' => $this->dispatch->dispatch_number,
            'outlet_name' => $this->dispatch->outlet->name,
            'title' => 'Product Request Received',
            'item_count' => $itemCount,
            'message' => "New product request {$this->dispatch->dispatch_number} received from {$this->dispatch->outlet->name} ({$itemCount} items).",
            'url' => route('dispatches.orders'),
        ];
    }
}
