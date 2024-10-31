<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = now()->subDays(random_int(1, 365))->subHours(random_int(1, 23))->subSeconds(random_int(1, 59));

        return [
            'text' => $this->faker->realText(),
            'user_id' => User::factory(),
            'created_at' => $date,
            'updated_at' => $date,
        ];
    }
}
