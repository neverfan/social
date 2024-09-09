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
            'password' => static::$password ??= fake()->password(),
            'first_name' => fake()->firstName($gender),
            'last_name' => fake()->lastName($gender),
            'gender' => $gender,
            'city' => fake()->city(),
            'birth_date' => fake()->dateTimeBetween('-120 years', '-14 years')->format('Y-m-d'),
            'biography' => fake()->sentences(1, 3),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
