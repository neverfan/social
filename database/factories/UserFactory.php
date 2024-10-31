<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(['male', 'female']);

        return [
            'password' => static::$password ??= fake()->password(),
            'first_name' => fake()->firstName($gender),
            'last_name' => fake()->lastName($gender),
            'gender' => $gender,
            'city' => fake()->city(),
            'birth_date' => fake()->dateTimeBetween('-120 years', '-14 years')->format('Y-m-d'),
            'biography' => fake()->sentences(1, 3),
            'friends' => collect(),
            'celebrity' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function celebrity(bool $celebrity = true): self
    {
        return $this->state(fn(array $attributes) => [
            'celebrity' => $celebrity,
        ]);
    }

    public function withFriends(Collection $friends = null): self
    {
        return $this->state(fn(array $attributes) => [
            'friends' => $friends ?? collect(),
        ]);
    }
}
