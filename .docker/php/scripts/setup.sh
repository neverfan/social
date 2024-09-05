#!/bin/sh

set -e

# Проверка наличия переменной REPLACE_INI
if [ -z "$REPLACE_INI" ]; then
    if [ ! "$RUNTIME_MODE" = "local-cli" ]; then
        echo "Переменная REPLACE_INI не установлена. Пропускаем перемещение файла php.ini"
    fi
else
  # Проверка существования файла
  if [ ! -f "$REPLACE_INI" ]; then
    echo "Файл $REPLACE_INI не существует. Пропускаем перемещение файла php.ini"
  else
    # Перемещение файла php.ini
    mv "$REPLACE_INI" "$PHP_INI_DIR/php.ini"
    echo "Файл $REPLACE_INI успешно перемещен в $PHP_INI_DIR/php.ini"
  fi
fi

if [ "$RUNTIME_MODE" = "fpm" ] && [ -f "/var/www/.docker/config/fpm-site.conf" ]; then
    cp -u /var/www/.docker/config/fpm-site.conf /usr/local/etc/php-fpm.d/900-site.conf
fi

if [ -f "/var/www/.docker/config/php.ini" ]; then
    cp -u /var/www/.docker/config/php.ini $PHP_INI_DIR/conf.d/zzz-php.ini
fi

if [ ! -z "$XDEBUG_MODE" ] && [ ! "$XDEBUG_MODE" = "off" ]; then
    sed -i 's/;zend_extension=xdebug.so/zend_extension=xdebug.so/' "$PHP_INI_DIR/conf.d/xdebug.ini"
fi
