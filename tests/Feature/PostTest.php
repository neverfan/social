<?php

namespace Tests\Feature;

use App\Events\PostCreated;
use App\Events\PostDeleted;
use App\Models\Post;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PostTest extends TestCase
{
    /**
     * Проверка чтения поста
     * @return void
     */
    public function testGetRoute(): void
    {
        $post = Post::factory()->create();

        $response = $this
            ->getJson(route('post.get', ['post' => $post->id]));

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'post' => [
                        'id',
                        'text',
                        'user_id',
                        'created_at',
                        'created_at'
                    ]
                ]
            ]);
    }


    /*
    * Проверка создания поста
    * @test
    */
    public function testCreateRoute(): void
    {
        $user = User::factory()->create();

        $data = [
            'text' => 'Test text',
        ];

        Event::fake();

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $user->token())
            ->postJson(route('post.create'), $data);

        Event::assertDispatched(PostCreated::class);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'post' => [
                        'id',
                        'text',
                        'user_id',
                        'created_at',
                        'created_at'
                    ]
                ]
            ]);

        $this->assertDatabaseHas('posts', [
            'text' => 'Test text',
            'user_id' => $user->id,
        ]);
    }

    /**
     * Проверка обновления поста
     * @return void
     */
    public function testUpdateRoute(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'text' => 'old text',
            'user_id' => $user->id,
        ]);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $user->token())
            ->putJson(route('post.update', ['post' => $post->id]), [
                'text' => 'new text',
            ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'update'
                ]
            ])
            ->assertJsonPath('data.update', true);

        $this->assertDatabaseHas('posts', [
            'text' => 'new text',
            'user_id' => $user->id
        ]);
    }

    /**
     * Проверка удаления поста
     * @return void
     */
    public function testDeleteRoute(): void
    {
        Post::factory()->count(5)->create();

        /** @var User $user */
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'text' => 'text',
            'user_id' => $user->id,
        ]);

        Event::fake();

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $user->token())
            ->putJson(route('post.delete', ['post' => $post->id]));

        Event::assertDispatched(PostDeleted::class);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'delete'
                ]
            ])
            ->assertJsonPath('data.delete', true);

        $this->assertEquals(5, Post::count());
        $this->assertDatabaseMissing('posts', [
            'text' => 'text',
            'user_id' => $user->id
        ]);
    }

    /**
     * Проверяет получение фида без кэша
     * @return void
     */
    public function testGetFeedWithoutCache(): void
    {
        $friendsCount = 10;
        $postCount = 5;
        $friends = UserFactory::new()->count($friendsCount)->create();
        $celebrity = UserFactory::new()->celebrity()->create();

        //самый старый пост от celebrity
        $celebrityPost = Post::factory()->create([
            'user_id' => $celebrity->id,
            'created_at' => now()->subHours(($friendsCount * $postCount)-10),
            'updated_at' => now()->subDays(($friendsCount * $postCount)-10),
        ]);

        $date = now()->subHours($friendsCount*$postCount);

        foreach ($friends as $index => $friend) {
            for ($i = 0; $i < $postCount; $i++) {
                $date = $date->addHour();
                Post::factory()->create([
                    'user_id' => $friend->id,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }

        $friends = $friends->push($celebrity);

        $user = User::factory()->create();
        $user->pushManyFriends($friends->pluck('id')->toArray());

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $user->token())
            ->getJson(route('post.feed', ['offset' => 40]));

        //проверяем, что пост знаменитости присутствует и он последний
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
               'data' => [
                   'posts' => [
                       '*' => ['id', 'user_id', 'text', 'created_at', 'updated_at']
                   ]
               ]
            ])
            ->assertJsonPath('data.posts.0.id', $celebrityPost->id);
    }
}
