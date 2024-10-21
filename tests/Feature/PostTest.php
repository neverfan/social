<?php

namespace Tests\Feature;

use App\Models\Post;
use Tests\TestCase;
use App\Models\User;

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

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $user->token())
            ->postJson(route('post.create'), $data);

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

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $user->token())
            ->putJson(route('post.delete', ['post' => $post->id]));

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
}
