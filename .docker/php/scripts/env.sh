#!/bin/sh

# Проверка наличия переменной REPLACE_ENV
if [ -z "$REPLACE_ENV" ]; then
    if [ ! "$RUNTIME_MODE" = "local-cli" ]; then
        echo "Переменная REPLACE_ENV не установлена. Пропускаем перемещение файла .env"
    fi
else
  # Проверка существования файла
  if [ ! -f "$REPLACE_ENV" ]; then
    echo "Файл $REPLACE_ENV не существует. Пропускаем перемещение файла .env"
  else
    mv /var/www/.env /var/www/old.env || true
    # Перемещение файла .env
    cp "/var/www/$REPLACE_ENV" /var/www/.env
    echo "Файл $REPLACE_ENV успешно перемещен в /var/www/.env"
  fi
fi
