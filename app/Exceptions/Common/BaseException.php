<?php

namespace App\Exceptions\Common;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Support\Response\Interfaces\ApiResponseInterface;
use Illuminate\Contracts\Container\BindingResolutionException;

class BaseException extends Exception
{
    /**
     * Возвращаемый код ошибки.
     */
    public const ERROR_CODE = 400;

    /**
     * Тип ошибки.
     */
    public const ERROR_TYPE = 'app_error';

    /**
     * Ключ описания ошибки.
     */
    public const ERROR_NAME = 'kp_exception';

    /**
     * Сообщение об ошибке.
     */
    public const ERROR_MESSAGE = 'Ошибка приложения';

    protected ApiResponseInterface $response;
    /**
     * BaseException constructor.
     *
     * @param string|null $message
     * @param int|null $code
     * @param Throwable|null $previous
     * @throws BindingResolutionException
     */
    public function __construct(string $message = null, int $code = null, Throwable $previous = null)
    {
        $this->response = app()->make(ApiResponseInterface::class);
        $this->message = $message ?? static::ERROR_MESSAGE;
        $this->code = !is_null($code) ? $code : static::ERROR_CODE;

        parent::__construct($this->message, $this->code, $previous);
    }

    /**
     * Вывести ошибку в json-формате.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function render(Request $request): JsonResponse
    {
        return $this->response->json([
            'errors' => [
                'exception' => [
                    'type' => static::ERROR_TYPE,
                    'message' => $this->message,
                    'name' => static::ERROR_NAME,
                ],
            ],
        ], $this->code);
    }
}
