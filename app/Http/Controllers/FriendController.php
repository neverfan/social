<?php

namespace App\Http\Controllers;

use App\Events\FriendPushed;
use App\Events\FriendRejected;
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
        /** @var User $user */
        $user = Auth::user();
        $user->pushFriend($friend->id);

        FriendPushed::dispatch($user, $friend);

        return $this->response->success();
    }

    /**
     * @param User $friend
     * @return JsonResponse
     */
    public function delete(User $friend): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $user->rejectFriend($friend->id);

        FriendRejected::dispatch($user, $friend);

        return $this->response->success();
    }
}
