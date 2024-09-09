# Social
Для практики курса Highload Architect

# Запуск приложения

1. Способ через `makefile`

Чтобы запустить приложение, выполните команду в корне проекта:

```bash
make setup
```

2. Способ без `makefile`

Чтобы запустить приложение, выполните следующие команды в корне проекта:

```bash
docker compose up -d && \
docker compose run --rm php-fpm composer install --ignore-platform-reqs && \
docker compose run --rm php-fpm php artisan migrate:fresh
```

## Документация

Коллекция Postman для тестирования API находится в папке `/docs/`.

## Стэк

- **Php-fpm**
- **Nginx**
- **Postgres**
