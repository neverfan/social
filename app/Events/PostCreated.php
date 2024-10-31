<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class PostCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public readonly int $postId;

    public readonly User $user;

    /**
     * Create a new event instance.
     */
    public function __construct(int $postId, User $user)
    {
        $this->postId = $postId;
        $this->user = $user;
    }
}
