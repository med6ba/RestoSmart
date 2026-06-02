<?php

namespace App\Events;

use App\Models\DeliveryMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public DeliveryMessage $deliveryMessage) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('delivery-chat.'.$this->deliveryMessage->order_id);
    }

    public function broadcastAs(): string
    {
        return 'delivery.message.sent';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $message = $this->deliveryMessage->loadMissing('sender');

        return [
            'message_id' => $message->id,
            'order_id' => $message->order_id,
            'delivery_id' => $message->delivery_id,
            'sender_id' => $message->sender_id,
            'receiver_id' => $message->receiver_id,
            'message' => $message->message,
            'sender_name' => $message->sender?->name ?? __('Unknown user'),
            'sender_role' => $message->sender?->role ?? 'unknown',
            'created_at' => $message->created_at?->toISOString(),
            'formatted_time' => $message->created_at?->format('H:i'),
        ];
    }
}
