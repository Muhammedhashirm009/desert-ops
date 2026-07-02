<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MaterialRequestCreated extends Notification
{
    use Queueable;

    protected $materialRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct(\App\Models\MaterialRequest $materialRequest)
    {
        $this->materialRequest = $materialRequest;
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
        $itemCount = $this->materialRequest->items()->count();
        return [
            'material_request_id' => $this->materialRequest->id,
            'request_number' => $this->materialRequest->request_number,
            'outlet_name' => 'Material Request',
            'title' => 'Material Request Submitted',
            'item_count' => $itemCount,
            'message' => "New material request {$this->materialRequest->request_number} submitted by {$this->materialRequest->requested_by} ({$itemCount} items).",
            'url' => route('material-requests.show', $this->materialRequest->id),
        ];
    }
}
