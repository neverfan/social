<?php

namespace App\Exceptions\Auth;

use App\Exceptions\Common\BaseException;

class AccessForbiddenException extends BaseException
{
    /**
     * Возвращаемый код ошибки.
     */
    public const ERROR_CODE = 403;

    public const ERROR_MESSAGE = 'Доступ запрещен';

    public const ERROR_NAME = 'access_forbidden';
}
