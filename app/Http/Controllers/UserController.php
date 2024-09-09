<?php

namespace App\Http\Controllers;

use App\Exceptions\Auth\UnauthorizedException;
use App\Http\Requests\ApiRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Requests\User\ShowRequest;
use App\Http\Requests\User\LoginRequest;
use App\Support\Auth\JwtToken;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $dataset = [
            'password' => Hash::make($request->get('password')),
            'first_name' => $request->get('first_name'),
            'last_name' => $request->get('last_name'),
            'gender' => $request->get('gender'),
            'city' => $request->get('city'),
            'birth_date' => $request->get('birth_date'),
            'biography' =>  $request->get('biography'),
            'updated_at' => now(),
            'created_at' => now(),
        ];

        $userId = DB::table('users')->insertGetId($dataset);

        return $this->response->success([
            'user' => $this->getUserById($userId, ['password'])
        ]);
    }

    /**
     * Логин
     * @param LoginRequest $request
     * @return JsonResponse
     * @throws UnauthorizedException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->getUserById($request->get('id'), null);

        if (!Hash::check($request->get('password'), $user['password'])) {
            throw new UnauthorizedException();
        }

        $token = JwtToken::generate($user['id']);

        return $this->response->success([
            'token' => $token->getBearer(),
            'expires_in' => $token->getExpiredAt()->timestamp,
        ]);
    }

    /**
     * Данные текущего пользователя
     * @param ApiRequest $request
     * @return JsonResponse
     */
    public function current(ApiRequest $request): JsonResponse
    {
        $decoded = JwtToken::decode($request->bearerToken());

        return $this->response->success([
            'user' => $this->getUserById($decoded->user_id, ['password']),
        ]);
    }

    /**
     * @param ShowRequest $request
     * @return JsonResponse
     */
    public function get(ShowRequest $request): JsonResponse
    {
        return $this->response->success([
            'user' => $this->getUserById($request->route('user_id'), ['password']),
        ]);
    }

    /**
     * Обновить токен
     * @param ApiRequest $request
     * @return JsonResponse
     */
    public function refresh(ApiRequest $request): JsonResponse
    {
        $decoded = JwtToken::decode($request->bearerToken());
        $token = JwtToken::generate($decoded->user_id);

        return $this->response->success([
            'token' => $token->getBearer(),
            'expires_in' => $token->getExpiredAt()->timestamp,
        ]);
    }

    /**
     * @param int $id
     * @param array|null $except
     * @return array
     */
    private function getUserById(int $id, array|null $except = ['id', 'password']): array
    {
        $rows = DB::select('select * from users where id = ?', [$id]);
        $user = collect($rows)->first();

        if (!$user) {
            throw new ModelNotFoundException();
        }

        $user = (array) $user;
        if ($except) {
            $user = Arr::except($user, $except);
        }

        return $user;
    }
}
