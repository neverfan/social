<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Events\FriendPushed;
use App\Events\FriendRejected;
use Illuminate\Support\Facades\Event;

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

        Event::fake();

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $user->token())
            ->putJson(route('friend.set', ['friend' => $fiendUser->id]));

        Event::assertDispatched(FriendPushed::class);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'friends' => collect([$fiendUser->id])->toJson(),
        ]);
    }

    /*
    * Проверка удаления пользователя из друзей
    * @test
    */
    public function testDelete(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $fiendUser = User::factory()->create();
        $fiendUser2 = User::factory()->create();
        $fiendUser3 = User::factory()->create();

        $user->pushManyFriends([$fiendUser->id, $fiendUser2->id]);
        $user->pushFriend($fiendUser3->id);

        Event::fake();

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $user->token())
            ->putJson(route('friend.delete', ['friend' => $fiendUser->id]));

        Event::assertDispatched(FriendRejected::class);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'friends' => collect([$fiendUser->id])->toJson(),
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'friends' => collect([$fiendUser2->id, $fiendUser3->id])->toJson(),
        ]);
    }
}
