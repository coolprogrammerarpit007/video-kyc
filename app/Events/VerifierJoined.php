<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VerifierJoined
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $sessionUuid;
    public int $verifierId;

    /**
     * Create a new event instance.
     */
    public function __construct(string $sessionUuid,int $verifierId)
    {
        $this->sessionUuid = $sessionUuid;
        $this->verifierId = $verifierId;
    }

    /**
     * Get the channels the event should broadcast on.
     * Which channel should this event received on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('kyc-session' . $this->sessionUuid),
        ];
    }


    /*

            Rename the event on broadcast

    */

    public function broadcastAs():string
    {
        return 'verifier.joined';
    }
}
