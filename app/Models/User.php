<?php

namespace App\Models;

use App\Support\Auth\JwtToken;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * @property Collection $friends
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const int CELEBRITY_FRIENDS_COUNT = 1000;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'password',
        'first_name',
        'second_name',
        'birthdate',
        'biography',
        'city',
        'friends',
        'celebrity',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'friends' => AsCollection::class,
        ];
    }

    public function token(): string
    {
        return JwtToken::generate($this->id)->getBearer();
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function pushFriend(int $userId): self
    {
        $this->friends = $this->friends->push($userId)->unique()->values()->sort();
        $this->save();

        return $this;
    }

    public function pushManyFriends(array $userIds): self
    {
        $this->friends = $this->friends->merge($userIds)
            ->unique()->values()->sort();

        $this->save();

        return $this;
    }

    public function rejectFriend(int $userId): self
    {
        $this->friends = $this->friends->reject(fn($friendId) => $friendId === $userId)
            ->unique()->values()->sort();

        $this->save();

        return $this;
    }

    public function isCelebrity(): bool
    {
        return $this->celebrity;
    }

    /**
     * Подписчики данного пользователя
     * @param bool $withCache
     * @return Collection
     */
    public function getSubscribers(bool $withCache = true): Collection
    {
        $key = "subscribers_user_{$this->id}";

        if (!$withCache) {
            Cache::forget($key);
        }

        return Cache::remember($key, config('cache.ttl'), fn() => $this
            ->newQuery()
            ->select('id')
            ->whereRaw("friends @> '[{$this->id}]'")
            ->pluck('id'));
    }

    /**
     * Подписчики данного пользователя
     * @param bool $withCache
     * @return int
     */
    public function getSubscribersCount(bool $withCache = true): int
    {
        $key = "subscribers_count_user_{$this->id}";

        if (!$withCache) {
            Cache::forget($key);
        }

        return Cache::remember($key, config('cache.ttl'), fn() => $this
            ->newQuery()
            ->whereRaw("friends @> '[{$this->id}]'")
            ->count());
    }

    /**
     * Добавить подписчиков для данного пользователя
     * @param Collection $subscribers
     * @return $this
     */
    public function addSubscribers(Collection $subscribers): self
    {
        $subscribers->each(fn(User $user) => $user->pushFriend($this->id));

        return $this;
    }

    /**
     * Получить друзей-знаменитостей данного пользователя
     * @return Collection
     */
    public function getCelebrityFriends(): Collection
    {
        return $this->newQuery()
            ->whereIn('id', $this->friends)
            ->where('celebrity', true)
            ->pluck('id');
    }
}
