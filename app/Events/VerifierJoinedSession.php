<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VerifierJoinedSession
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**p
     * Create a new event instance.
     */

    public string $sessionId;
    public int $verifierId;
    public function __construct(string $sessionId,int $verifierId)
    {
        $this->sessionId = $sessionId;
        $this->verifierId = $verifierId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('kyc-session' . $this->sessionId),
        ];
    }

    public function broadcastAs():string
    {
        return 'verifier.joined.session';
    }
}
