<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessageSent implements ShouldBroadcast
{
    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new PrivateChannel("chat.{$this->message->destiny_id}");
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'message' => $this->message->message,
            'origin_id' => $this->message->origin_id,
            'destiny_id' => $this->message->destiny_id,
            'created_at' => $this->message->created_at->toDateTimeString(),
        ];
    }
}

