<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class FriendTest extends TestCase
{
    /*
    * Проверка регистрации пользователя
    * @test
    */
    public function testSet(): void
    {
        $user = User::factory()->create();
        $fiendUser = User::factory()->create();

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $user->token())
            ->putJson(route('friend.set', ['friend' => $fiendUser->id]));

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_friends', [
            'user_id' => $user->id,
            'friend_id' => $fiendUser->id,
        ]);
    }

    /*
    * Проверка удаления пользователя из друзей
    * @test
    */
    public function testDelete(): void
    {
        $user = User::factory()->create();
        $fiendUser = User::factory()->create();
        $user->friends()->attach($fiendUser);

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $user->token())
            ->putJson(route('friend.delete', ['friend' => $fiendUser->id]));

        $response->assertStatus(200);

        $this->assertDatabaseMissing('user_friends', [
            'user_id' => $user->id,
            'friend_id' => $fiendUser->id,
        ]);
    }
}
