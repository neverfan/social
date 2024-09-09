<?php

namespace App\Support\Response\Interfaces;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\AbstractPaginator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Interface ApiResponseInterface.
 * @package App\Support\Response\Interfaces
 */
interface ApiResponseInterface
{
    /**
     * Вернуть ответ в виде json.
     *
     * @param array $data
     * @param int $code
     * @return JsonResponse
     */
    public function json(array $data, int $code = Response::HTTP_OK): JsonResponse;

    /**
     * Вернуть ответ содержащий данные.
     *
     * @param array $data
     * @param int $code
     * @param array $meta
     * @return Response
     */
    public function success(array $data = [], int $code = Response::HTTP_OK, array $meta = []): Response;

    /**
     * Вернуть ответ содержащий пагинатор
     *
     * @param AbstractPaginator $paginator
     * @param int $code
     * @return Response
     */
    public function paginate(AbstractPaginator $paginator, int $code = Response::HTTP_OK): Response;

    /**
     * Вернуть ответ содержащий ошибки.
     *
     * @param array $errors
     * @param array $data
     * @return Response
     */
    public function fail(array $errors, array $data = []): Response;

    /**
     * Вернуть ответ в поток в виде бинарного файла.
     *
     * @param string $content
     * @param string $filename
     * @param string $contentType
     * @return StreamedResponse
     */
    public function stream(string $content, string $filename, string $contentType): StreamedResponse;
}
