<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class FriendController extends Controller
{
    /**
     * @param User $friend
     * @return JsonResponse
     */
    public function set(User $friend): JsonResponse
    {
        Auth::user()->friends()->attach($friend);

        return $this->response->success();
    }

    /**
     * @param User $friend
     * @return JsonResponse
     */
    public function delete(User $friend): JsonResponse
    {
        Auth::user()->friends()->detach($friend);

        return $this->response->success();
    }
}
