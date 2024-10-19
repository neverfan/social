<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Auth\JwtToken;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    /*
    * Проверка регистрации пользователя
    * @test
    */
    public function testRegister(): void
    {
        $data = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'gender' => 'male',
            'city' => 'Test City',
            'birth_date' => '2000-01-01',
            'biography' => 'Test biography',
            'password' => 'password',
        ];

        $response = $this->postJson('/api/user/register', $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user_id'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'gender' => 'male',
            'city' => 'Test City',
            'birth_date' => '2000-01-01',
            'biography' => 'Test biography',
        ]);
    }

    /*
    * Проверка логина пользователя
    * @test
    */
    public function testLogin(): void
    {
        User::factory()->createMany(10);

        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/user/login', [
            'id' => $user->id,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'expires_in',
                ]
            ]);
    }

    /*
    * Проверка получения данных текущего пользователя
    * @test
    */
    public function testGetCurrent(): void
    {
        User::factory()->createMany(10);

        $user = User::factory()->create();

        $token = JwtToken::generate($user->id);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token->getBearer())
            ->getJson('/api/user/current');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user' => [
                        'id',
                        'first_name',
                        'last_name',
                        'gender',
                        'city',
                        'birth_date',
                        'biography',
                        'updated_at',
                        'created_at',
                    ],
                ]
            ]);

        $response->assertJsonFragment([
            'id' => $user->id,
        ]);
    }

    /*
    * Проверка получения данных пользователя по id
    * @test
    */
    public function testGetUser(): void
    {
        User::factory()->createMany(10);

        $user = User::factory()->create();

        $response = $this->getJson('/api/user/get/' . $user->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user' => [
                        'id',
                        'first_name',
                        'last_name',
                        'gender',
                        'city',
                        'birth_date',
                        'biography',
                        'updated_at',
                        'created_at',
                    ],
                ]
            ]);

        $response->assertJsonFragment([
            'id' => $user->id,
        ]);
    }

    /*
    * Проверка обновления токена
    * @test
    */
    public function testRefresh(): void
    {
        User::factory()->createMany(10);
        $user = User::factory()->create();
        $token = JwtToken::generate($user->id);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token->getBearer())
            ->getJson('/api/user/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'expires_in',
                ]
            ]);
    }
}
