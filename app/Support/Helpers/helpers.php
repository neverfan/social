<?php

if (!function_exists('app_version')) {
    /**
     * Получение версии приложения из файла, сгенерированного при деплое.
     *
     * @return string
     */
    function app_version(): string
    {
        $pathVersion = base_path('VERSION');
        if (!file_exists($pathVersion)) {
            return 'unknown';
        }

        return trim(file_get_contents($pathVersion));
    }
}

if (!function_exists('git_revision')) {
    /**
     * Получение ревизии приложения из файла, сгенерированного при деплое.
     * Для локального окружения пытаемся получить актуальную ревизию с помощью git консоли.
     *
     * @return string
     */
    function git_revision()
    {
        $pathRevision = base_path('REVISION');
        if (!file_exists($pathRevision)) {
            return 'unknown';
        }

        return trim(file_get_contents($pathRevision));
    }
}
