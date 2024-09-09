<?php

namespace App\Exceptions\Common;

class PageNotFoundException extends BaseException
{
    /**
     * Возвращаемый код ошибки.
     */
    public const ERROR_CODE = 404;

    /**
     * Текст ошибки.
     */
    public const ERROR_MESSAGE = 'Запрашиваемая страница не существует';

    /**
     * Ключ описания ошибки.
     */
    public const ERROR_NAME = 'page_not_found';
}
