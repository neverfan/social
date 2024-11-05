# Кэширование

## 1. Логика инвалидации кэша

### По времени

По умолчанию кэш инвалидируется по TTL-значению, значение задается в конфиге `config/cache.php`

### По событиям

Для инвалидации кэша используется система событий и слушателей Laravel.

#### События:
 - [PostCreated](../app/Events/PostCreated.php) - пользователь создал пост
 - [PostDeleted](../app/Events/PostDeleted.php) - пользователь удалил пост
 - [FriendPushed](../app/Events/FriendPushed.php) - пользователь добавил друга
 - [FriendRejected](../app/Events/FriendRejected.php) - пользователь удалил друга

#### Слушатели событий (обработчики):
 - [DispatchUpdateFeeds](../app/Listeners/DispatchUpdateFeeds.php) - слушатель событий [PostCreated](../app/Events/PostCreated.php), [PostDeleted](../app/Events/PostDeleted.php)
 - [DispatchUpdateFeedByUpdateFriends](../app/Listeners/DispatchUpdateFeedByUpdateFriends.php) - слушатель событий [FriendPushed](../app/Events/FriendPushed.php), [FriendRejected](../app/Events/FriendRejected.php)

##### Логика обработчика [DispatchUpdateFeeds](../app/Listeners/DispatchUpdateFeeds.php)

1. Из данных события берется запись поста
2. Берем пользователя опубликовавшего этот пост
3. Если пользователь "знаменитость" (кол-во подписчиков > [User::CELEBRITY_FRIENDS_COUNT](../app/Models/User.php)), тогда ничего не делаем
4. Для остальных пользователей, отправляем в очередь пакет задач [UpdateUserFeedCacheJob](../app/Jobs/UpdateUserFeedCacheJob.php) для коллекции его подписчиков, у которых будет обновлена новостная лента в кэше

##### Логика обработчика [DispatchUpdateFeedByUpdateFriends](../app/Listeners/DispatchUpdateFeedByUpdateFriends.php)

1. Добавляем в очередь задачу [UpdateUserFeedCacheJob](../app/Jobs/UpdateUserFeedCacheJob.php) на обновление кэша пользователя, который добавил/удалил друга

#### Какие данные новостных лент хранятся в кэше?

В кэше хранятся только `id` и `created_at` (дата создания) последних 1000 постов друзей пользователя.

 - В кэше сами тексты не хранятся, чтобы не тригерить сброс кэша при обновлении текста постов.
Так же можно не хранить дату создания поста если мы уверены, что id постов идут строго по порядку,
тогда для выборки постов знаменитостей можно ограничивать запрос по id, а не по created_at, что позволит сократить объем кэша в 2-3 раза.

#### Как выводятся посты новостных лент?

При обращении к роуту фида выполняется запрос в БД с использованием кэша (id последних постов друзей пользователя) + посты знаменитостей, на которых подписан пользователь (с датой больше самого старого поста обычных пользователей из кэша):

```php
return Post::query()
    ->whereIn('posts.id', $postIds->keys())
    ->orWhere(fn(Builder $query) => $query
        //Добавляем в выборку посты знаменитостей за период всех постов в кэше
        ->whereIn('user_id', $this->user->getCelebrityFriends())//знаменитости на которых подписан текущий пользователь
        ->whereBetween('posts.created_at', [
            Carbon::parse($postIds->first()),
            Carbon::now(),
        ]))
    ->orderByDesc('created_at')
    ->offset($offset)
    ->limit($limit)
    ->get();
```

Тексты постов подгружаются динамически из БД, что позволяет не инвалидировать кэш лент подписчиков при обновлении текста поста, при этом мы не создаем лишнюю нагрузку на БД т.к. ограничиваем запрос по конкретным `id` и добавляем `limit` к запросу

## 2. Перестройки кешей из СУБД

### Построение кэша из БД для текущего пользователя

При выводе новостной ленты, если по какой-то причине кэш для нее не сформирован, то он будет сформирован динамически, логика описана в классе [App\Support\Services\Feed](../app/Support/Services/Feed/Feed.php)

### Прогрев кэша при холодном старте приложения

На случай, если приложение запускается при отсутствующем кэше, реализована команда [App\Console\Commands\CacheWarming](../app/Console/Commands/CacheWarming.php) для прогрева кэша.

Команда и опции описаны в [README.md](../README.md)

#### Логика работы команды прогрева кэша

1. Определяем "активных" пользователей через `SQL`-запрос (для примера берем тех кто входил в соцсеть за последние n - дней)
2. Отправляем в очередь пакеты формирования кэшей активных пользователей, в один пакет добавляется 5000 пользователей
3. За формирование кэша отвечает все та же задача [UpdateUserFeedCacheJob](../app/Jobs/UpdateUserFeedCacheJob.php)
4. Процесс генерации пакетов отображается в консоли, процесс обработки задач отображается в дашборде [Horizon](http://localhost/horizon/dashboard) 
