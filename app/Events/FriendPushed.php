<?php

namespace App\Events;

use App\Models\Post;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FriendPushed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public readonly User $user;

    public readonly User $friend;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, User $friend)
    {
        $this->user = $user;
        $this->friend = $friend;
    }
}
