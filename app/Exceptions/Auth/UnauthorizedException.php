<?php

namespace App\Exceptions\Auth;

use App\Exceptions\Common\BaseException;

class UnauthorizedException extends BaseException
{
    /**
     * Возвращаемый код ошибки.
     */
    public const ERROR_CODE = 401;

    public const ERROR_MESSAGE = 'Пользователь не авторизован';

    public const ERROR_NAME = 'unauthorized';
}
