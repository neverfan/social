<?php

namespace App\Events;

use App\Models\Post;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostDeleted
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
