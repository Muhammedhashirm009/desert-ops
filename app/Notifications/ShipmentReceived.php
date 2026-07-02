<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ShipmentReceived extends Notification
{
    use Queueable;

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
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $this->dispatch->load('outlet');
        $itemCount = $this->dispatch->items()->count();
        return [
            'dispatch_id' => $this->dispatch->id,
            'dispatch_number' => $this->dispatch->dispatch_number,
            'outlet_name' => $this->dispatch->outlet->name,
            'title' => 'Shipment Received',
            'item_count' => $itemCount,
            'message' => "Shipment {$this->dispatch->dispatch_number} has been received by {$this->dispatch->outlet->name} ({$itemCount} items).",
            'url' => route('dispatches.show', $this->dispatch->id),
        ];
    }
}
