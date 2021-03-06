<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MovieDisliked implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $movie;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($movie)
    {
        $this->movie = $movie;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('dislikes', $this->movie);
    }
}
