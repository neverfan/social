<?php

namespace App\Http\Controllers;

use App\Exceptions\Auth\UnauthorizedException;
use App\Http\Requests\ApiRequest;
use App\Http\Requests\User\RegisterRequest;
use App\Http\Requests\User\SearchRequest;
use App\Http\Requests\User\GetRequest;
use App\Http\Requests\User\LoginRequest;
use App\Models\User;
use App\Support\Auth\JwtToken;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Query\Builder;
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
        $data = array_merge($request->safe()->toArray(), [
            'password' => Hash::make($request->get('password')),
            'updated_at' => now(),
            'created_at' => now(),
        ]);

        return $this->response->success([
            'user_id' => DB::table('users')->insertGetId($data)
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
     * Данные текущего пользователя
     * @param ApiRequest $request
     * @return JsonResponse
     */
    public function current(ApiRequest $request): JsonResponse
    {
        $decoded = JwtToken::decode($request->bearerToken());

        /** @var User $user */
        $user = $request->user();

        return $this->response->success([
            'user' => $user->toArray(),
            'subscribers' => [
                'count' => $user->getSubscribersCount(),
            ],
        ]);
    }

    /**
     * @param User $user
     * @return JsonResponse
     */
    public function get(User $user): JsonResponse
    {
        return $this->response->success([
            'user' => $user,
        ]);
    }

    /**
     * @param SearchRequest $request
     * @return JsonResponse
     */
    public function search(SearchRequest $request): JsonResponse
    {
        $paginator = DB::table('users')
            ->select([
                'id', 'first_name', 'last_name', 'birth_date', 'city',
            ])
            ->when($request->get('first_name'), function (Builder $query) use ($request) {
                $query->whereRaw('lower(first_name) like :first_name', ['first_name' => mb_strtolower($request->get('first_name')) . '%']);
                //$query->whereRaw('first_name ilike :first_name', ['first_name' => $request->get('first_name') . '%']);
            })
            ->when($request->get('last_name'), function (Builder $query) use ($request) {
                $query->whereRaw('lower(last_name) like :last_name', ['last_name' => mb_strtolower($request->get('last_name')) . '%']);
                //$query->whereRaw('last_name ilike :last_name', ['last_name' => $request->get('last_name') . '%']);
            })
            ->orderBy('id')
            ->paginate(
                perPage: $request->get('limit', 20),
                page: $request->get('page', 1)
            );

        return $this->response->paginate($paginator);
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
