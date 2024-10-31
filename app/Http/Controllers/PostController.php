<?php

namespace App\Http\Controllers;

use App\Events\PostCreated;
use App\Events\PostDeleted;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Support\Services\Feed\Feed;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Post\FeedRequest;
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
        $post = $request->user()->posts()->create($request->safe()->toArray());

        PostCreated::dispatch($post->id, $request->user());

        return $this->response->success([
            'post' => $post
        ]);
    }

    /**
     * Обновление поста.
     * (!) Не влияет на обновление кэша фидов т.к. в кэше хранится только id постов
     * и обновление поста не влияет на порядок его вывода.
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

        if (!$deleted = $post->delete()) {
            return $this->response->failed(500);
        }

        PostDeleted::dispatch($post->id, $request->user());

        return $this->response->success([
            'delete' => $deleted,
        ]);
    }

    /**
     * @param FeedRequest $request
     * @return JsonResponse
     */
    public function feed(FeedRequest $request): JsonResponse
    {
        return $this->response->success([
            'posts' => (new Feed($request->user()))
                ->posts(
                    $request->get('offset', 0),
                    $request->get('limit', 20)
                ),
            ]);
    }
}
