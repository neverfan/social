<?php

namespace App\Exceptions;

use App\Exceptions\Auth\UnauthorizedException;
use App\Exceptions\Common\PageNotFoundException;
use App\Support\Response\Interfaces\ApiResponseInterface;
use Exception;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExceptionResolver
{

    public function __construct(
        private Exceptions $exceptions) {
    }

    public function register(): void
    {
        $this->exceptions->render(function (Exception $exception, Request $request) {
            if ($exception instanceof ValidationException) {
                return app()->make(ApiResponseInterface::class)
                    ->fail($exception->validator->errors()->getMessages());
            }

            if ($exception instanceof NotFoundHttpException) {
                return (new PageNotFoundException())->render($request);
            }

            if ($exception instanceof ModelNotFoundException) {
                return (new PageNotFoundException())->render($request);
            }

            if ($exception instanceof SignatureInvalidException) {
                return (new UnauthorizedException())->render($request);
            }

            return $exception;
        });
    }
}
