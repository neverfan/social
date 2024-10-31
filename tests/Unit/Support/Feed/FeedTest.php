<?php

namespace Tests\Unit\Support\Feed;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use App\Support\Services\Feed\Feed;
use Database\Factories\UserFactory;

class FeedTest extends TestCase
{
    /**
     * Проверяет, что пост знаменитости попадает в выборку
     */
    public function testGetFeedMethodShouldReturnFeedInstance(): void
    {
        $friendsCount = 10;
        $postCount = 5;
        $friends = UserFactory::new()->count($friendsCount)->create();
        $celebrity = UserFactory::new()->celebrity()->create();

        //самый старый пост от celebrity
        $celebrityPost = Post::factory()->create([
            'user_id' => $celebrity->id,
            'created_at' => now()->subHours(($friendsCount*$postCount)-10),
            'updated_at' => now()->subDays(($friendsCount*$postCount)-10),
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

        $posts = (new Feed($user))->posts(0, 100);

        $this->assertCount(51, $posts);
        $this->assertEquals($celebrityPost->id, $posts->where('user_id', $celebrity->id)->first()->id);
    }
}
