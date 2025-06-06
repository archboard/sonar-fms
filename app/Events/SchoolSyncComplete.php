<?php

namespace App\Events;

use App\Models\School;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SchoolSyncComplete
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public School $school;

    /**
     * Create a new event instance.
     */
    public function __construct(School $school)
    {
        $this->school = $school;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
