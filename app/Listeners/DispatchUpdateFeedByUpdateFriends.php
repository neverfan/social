<?php

namespace App\Listeners;

use App\Events\FriendPushed;
use App\Events\FriendRejected;
use App\Jobs\UpdateUserFeedCacheJob;
use Illuminate\Contracts\Queue\ShouldQueue;

class DispatchUpdateFeedByUpdateFriends implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(FriendPushed|FriendRejected $event): void
    {
        UpdateUserFeedCacheJob::dispatch($event->user->id);
    }
}
