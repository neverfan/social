<?php

namespace App\Support\Services\Feed;

use Carbon\Carbon;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Feed
{
    public const int CACHE_POST_LIMIT = 1000;

    public function __construct(
        private readonly User $user,
    ){
    }

    /**
     * Возвращает посты для ленты, берет id постов из кэша и подмешивает в запрос посты знаменитостей
     * @param int $offset
     * @param int $limit
     * @return Collection
     */
    public function posts(int $offset = 0, int $limit = 20): Collection
    {
        $postIds = $this->withCache();

        if($postIds->isEmpty()) {
            return collect();
        }

        return Post::query()
            ->whereIn('posts.id', $postIds->keys())
            ->orWhere(fn(Builder $query) => $query
                //Добавляем в выборку посты знаменитостей за период всех постов в кэше
                ->whereIn('user_id', $this->user->getCelebrityFriends())
                ->whereBetween('posts.created_at', [
                    Carbon::parse($postIds->first()),
                    Carbon::now(),
                ]))
            ->orderByDesc('created_at')
            ->offset($offset)
            ->limit($limit)
            ->get();
    }

    public function updateCache(): self
    {
        $this->withCache(true);

        return $this;
    }

    public function forgetCache(): self
    {
        Cache::forget($this->getCacheKey());

        return $this;
    }

    private function withCache(bool $invalidate = false): Collection
    {
        if ($invalidate) {
            $this->forgetCache();
        }

        $postIds = Cache::remember($this->getCacheKey(), config('cache.ttl'), fn() => $this->getPostIds()->toArray());

        return collect($postIds);
    }

    /**
     * Получаем id постов фида и дату их создания.
     *
     * В кэше сами тексты не храним, чтобы не тригерить сброс кэша при обновлении текста поста.
     * Так же можно не использовать дату создания поста если мы уверены, что id постов идут строго по порядку,
     * тогда для выборки постов знаменитостей можно ограничивать запрос по id, а не по created_at, это позволит сократить объем кэша в 2-3 раза.
     * @return Collection
     */
    private function getPostIds(): Collection
    {
        return DB::table('posts')
            ->select(['id','created_at'])
            ->whereIn('user_id', $this->user->friends)
            ->orderByDesc('created_at')
            ->limit(self::CACHE_POST_LIMIT)
            ->get()
            ->transform(fn($item) => [
                'id' => $item->id,
                'created_at' => $item->created_at,
            ])
            ->pluck('created_at', 'id');
    }

    private function getCacheKey(): string
    {
        return "feed_user_{$this->user->id}";
    }
}
