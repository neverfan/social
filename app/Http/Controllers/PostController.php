<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\FeedRequest;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Post\CreateRequest;
use App\Http\Requests\Post\UpdateRequest;

class PostController extends Controller
{
    /**
     * @param Post $post
     * @return JsonResponse
     */
    public function get(Post $post): JsonResponse
    {
        return $this->response->success([
            'post' => $post,
        ]);
    }

    /**
     * @param CreateRequest $request
     * @return JsonResponse
     */
    public function create(CreateRequest $request): JsonResponse
    {
        return $this->response->success([
            'post' => Auth::user()->posts()->create($request->safe()->toArray())
        ]);
    }

    /**
     * @param Post $post
     * @param UpdateRequest $request
     * @return JsonResponse
     */
    public function update(Post $post, UpdateRequest $request): JsonResponse
    {
        if ($request->user()->cannot('update', $post)) {
            return $this->response->failed(403);
        }

        return $this->response->success([
            'update' => $post->update($request->safe()->toArray())
        ]);
    }

    /**
     * @param Post $post
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Post $post, Request $request): JsonResponse
    {
        if ($request->user()->cannot('delete', $post)) {
            return $this->response->failed(403);
        }

        return $this->response->success([
            'delete' => $post->delete(),
        ]);
    }

    /**
     * @param FeedRequest $request
     * @return JsonResponse
     */
    public function feed(FeedRequest $request): JsonResponse
    {
        //todo: вывести ленту постов всех друзей
        //todo: В ленте держать последние 1000 обновлений друзей
        //todo: Лента должна кешироваться
        //todo: (опционально) Обновление лент работает через очередь.
        //todo: Есть возможность перестройки кешей из СУБД.

        return $this->response->success([

        ]);
    }
}
