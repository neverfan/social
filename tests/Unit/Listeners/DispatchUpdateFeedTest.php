<?php

namespace Tests\Unit\Listeners;

use App\Events\PostCreated;
use App\Events\PostDeleted;
use App\Jobs\UpdateUserFeedCacheJob;
use App\Listeners\DispatchUpdateFeeds;
use Database\Factories\PostFactory;
use Database\Factories\UserFactory;
use Illuminate\Bus\PendingBatch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DispatchUpdateFeedTest extends TestCase
{
    /**
     * Проверяет, что обработка события ДОБАВЛЕНИЯ поста у знаменитости не приводит к запуску пакетной перестройки кэша
     */
    public function testDispatcherWithPostCreatedShouldNotPushUpdateCacheJobs(): void
    {
        Bus::fake();

        $subscribers = UserFactory::new()->count(3)->create();
        $celebrity = UserFactory::new()->celebrity()->create();

        $celebrity->addSubscribers($subscribers);

        $post = $celebrity->posts()->create(PostFactory::new()->make()->toArray());

        $dispatcher = new DispatchUpdateFeeds();
        $event = new PostCreated($post->id, $celebrity);

        $dispatcher->handle($event);

        Bus::assertNothingBatched();
    }

    /**
     * Проверяет, что обработка события УДАЛЕНИЯ поста у знаменитости не приводит к запуску пакетной перестройки кэша
     */
    public function testDispatcherWithPostDeletedShouldNotPushUpdateCacheJobs(): void
    {
        Bus::fake();

        $subscribers = UserFactory::new()->count(3)->create();
        $celebrity = UserFactory::new()->celebrity()->create();

        $celebrity->addSubscribers($subscribers);

        $post = $celebrity->posts()->create(PostFactory::new()->make()->toArray());

        $dispatcher = new DispatchUpdateFeeds();
        $event = new PostDeleted($post->id, $celebrity);

        $dispatcher->handle($event);

        Bus::assertNothingBatched();
    }

    /**
     * Проверяет, что событие создания поста у обычного пользователя приводит к запуску пакетной перестройки кэша
     * @return void
     */
    public function testDispatcherWithPostCreatedShouldPushUpdateCacheJobs(): void
    {
        Bus::fake();

        $subscribers = UserFactory::new()->count(3)->create();
        $user = UserFactory::new()->create();

        $user->addSubscribers($subscribers);

        $post = $user->posts()->create(PostFactory::new()->make()->toArray());

        $dispatcher = new DispatchUpdateFeeds();
        $event = new PostCreated($post->id, $user);

        $dispatcher->handle($event);

        $batchName = "feeds::cache::update::post_{$post->id}";

        /**
         * Запущена пакетная перестройка кэша фидов для 3 подписчиков
         */
        Bus::assertBatched(static fn(PendingBatch $batch) => $batch->name === $batchName && $batch->jobs->count() === $subscribers->count());
    }
}
