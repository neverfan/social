<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

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
            'password' => static::$password ??= 'password',
            'first_name' => fake()->firstName($gender),
            'last_name' => fake()->lastName($gender),
            'gender' => $gender,
            'city' => fake()->city(),
            'birth_date' => now()->subDays((365 * random_int(18, 55)) - random_int(1, 365)),
            'biography' => fake()->sentences(1, 3),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
