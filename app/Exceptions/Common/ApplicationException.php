<?php

namespace App\Exceptions\Common;

class ApplicationException extends BaseException
{
    public const ERROR_NAME = 'system_error';

    public const ERROR_MESSAGE = 'Не удалось выполнить запрос. Попробуйте повторить позже.';
}
