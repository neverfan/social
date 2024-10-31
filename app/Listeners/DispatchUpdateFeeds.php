<?php

namespace App\Listeners;

use App\Events\PostCreated;
use App\Events\PostDeleted;
use App\Jobs\UpdateUserFeedCacheJob;
use App\Models\User;
use Illuminate\Bus\Batch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Bus;

/**
 * Создает batch с задачами обновления фидов подписчиков пользователя создавшего пост
 */
class DispatchUpdateFeeds implements ShouldQueue
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
    public function handle(PostCreated|PostDeleted $event): void
    {
        /**
         * Если знаменитость, тогда пропускаем.
         * (посты знаменитостей интегрируются в ленты подписчиков через дополнительный запрос при получении всей ленты)
         */
        if ($event->user->isCelebrity() || ($subscribers = $event->user->getSubscribers(false))->isEmpty()) {
            return;
        }

        //генерируем таски для обновления кеша лент
        $jobs = $subscribers->transform(fn(int $userId) => new UpdateUserFeedCacheJob($userId));

        Bus::batch($jobs)
            ->name("feeds::cache::update::post_{$event->postId}")
            ->allowFailures()
            ->finally(function (Batch $batch) use ($subscribers) {
                //обработка батча завершена
            })
            ->dispatch();
    }
}
