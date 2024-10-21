<?php

namespace App\Support\Response;

use Illuminate\Support\Arr;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\AbstractPaginator;
use App\Support\Presenters\PresentProducerTrait;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Support\Response\Interfaces\ApiResponseInterface;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

/**
 * Class Response.
 *
 * @package App\Support\Response
 */
class Response implements ApiResponseInterface
{
    use PresentProducerTrait;

    /**
     * Вернуть ответ подготовленной структуры.
     *
     * @param array $data
     * @param int $code
     * @return JsonResponse
     */
    public function json(array $data = [], int $code = BaseResponse::HTTP_OK): JsonResponse
    {
        $data = $this->addAppRevision($data);
        $data = $this->removeDebug($data);

        return response()->json($data, $code, [
            'X-Robots-Tag' => 'noindex, nofollow, noarchive',
        ]);
    }

    /**
     * @param mixed $data
     * @param int $code
     * @param array $meta
     * @return JsonResponse
     */
    public function success(mixed $data = [], int $code = BaseResponse::HTTP_OK, array $meta = []): JsonResponse
    {
        return $this->json([
            'data' => $this->producePresent($data),
            'meta' => $this->producePresent($meta),
        ], $code);
    }

    /**
     * Вернуть ответ содержащий ошибки.
     *
     * @param $errors
     * @param array $data
     * @param int $code
     * @return JsonResponse
     */
    public function fail($errors, array $data = [], int $code = BaseResponse::HTTP_BAD_REQUEST): JsonResponse
    {
        return $this->json([
            'errors' => $errors,
            'data' => $this->producePresent($data),
        ], $code);
    }

    /**
     * Вернуть ответ с кодом ошибки.
     *
     * @param int $code
     * @return JsonResponse
     */
    public function failed(int $code = BaseResponse::HTTP_BAD_REQUEST): JsonResponse
    {
        return $this->json([
            'data' => [],
        ], $code);
    }

    /**
     * Вернуть данные с пагинатором
     *
     * @param AbstractPaginator $paginator
     * @param int $code
     * @param array $meta
     * @return JsonResponse
     */
    public function paginate(AbstractPaginator $paginator, int $code = BaseResponse::HTTP_OK, array $meta = []): JsonResponse
    {
        $data = $this->producePresent($paginator);

        if (!empty($meta)) {
            $data['meta'] = $this->producePresent($meta);
        }

        return $this->json($data, $code);
    }

    /**
     * Вернуть ответ в поток в виде бинарного файла.
     *
     * @param string $content
     * @param string $filename
     * @param string $contentType
     * @return StreamedResponse
     */
    public function stream(string $content, string $filename, string $contentType): StreamedResponse
    {
        $headers = [
            'Content-type' => $contentType,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Transfer-Encoding' => 'binary',
        ];

        return response()->stream(function () use ($content) {
            echo $content;
        }, 200, $headers);
    }

    /**
     * Добавление информации о версии приложения и ревизии в ответ api.
     *
     * @param array $data
     * @return array
     */
    private function addAppRevision(array $data): array
    {
        $data = Arr::add($data, 'meta.version', app_version());
        $data = Arr::add($data, 'meta.revision', git_revision());

        return $data;
    }

    private function removeDebug(array $data): array
    {
        if (!config('app.debug', false)) {
            unset($data['meta']['debug']);
        }

        return $data;
    }
}
