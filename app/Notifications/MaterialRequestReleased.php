<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MaterialRequestReleased extends Notification
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
            'outlet_name' => 'Material Release',
            'title' => 'Materials Released',
            'item_count' => $itemCount,
            'message' => "Materials for request {$this->materialRequest->request_number} have been released to the kitchen.",
            'url' => route('material-requests.show', $this->materialRequest->id),
        ];
    }
}
