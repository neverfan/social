<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Factories\PostFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = 1;
        $friendCount = 150;
        $postPerFriend = 10;
        $friendId = 100;
        $friends = [];
        for ($i = 0; $i < $friendCount; $i++) {
            PostFactory::new()
                ->count($postPerFriend)
                ->create([
                    'user_id' => $friendId,
                    'created_at' => function () {
                        return now()->subDays(random_int(0, 90))->subHours(random_int(0, 23));
                    },
                ]);

            $friends[] = $friendId++;
        }

        User::query()->firstWhere('id', $userId)->friends()->syncWithoutDetaching($friends);
    }
}
