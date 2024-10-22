ARG BUILD_VERSION="8.3"

FROM php:${BUILD_VERSION}-fpm

ARG DEBIAN_FRONTEND=noninteractive
ARG DEBCONF_NOWARNINGS=yes
ARG NONINTERACTIVE=1

ENV RUNTIME_MODE fpm
ENV COMPOSER_HOME /usr/local/lib/composer
ENV COMPOSER_CACHE_DIR /tmp/cache
ENV XDEBUG_MODE off
ENV PICKLE_PECL_UNSAFE yes

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN apt-get update && apt-get upgrade -y && apt-get install -y --quiet lsb-release gnupg apt-utils nano netbase curl wget postgresql-client supervisor && \
    IPE_ASPELL_LANGUAGES='en ru' install-php-extensions @composer opcache openssl imap mysqli libxml simplexml intl zip exif Imagick/imagick@master gd sockets pgsql pdo pdo_mysql pdo_pgsql bcmath pcntl pspell tidy redis xdebug && \
    rm $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini && \
    apt-get clean -y && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY php.ini "$PHP_INI_DIR/conf.d/yyy-project-php.ini"

RUN mkdir -p /usr/local/lib/composer /usr/local/scripts /usr/local/etc/php-exts /tmp/cache
COPY scripts/*.sh /usr/local/scripts/

COPY xdebug.ini "$PHP_INI_DIR/conf.d/xdebug.ini"

ENV PATH="/usr/local/bin:${PATH}"

RUN usermod -u 1000 www-data && groupmod -g 1000 www-data  && \
    chmod -R 774 /usr/local/lib/composer && chown -R 1000 /usr/local/lib/composer && \
    chmod -R 774 /tmp/cache && chown -R 1000 /tmp/cache && \
    chmod -R +x /usr/local/bin/ /usr/local/scripts/

COPY fpm-pool.conf /usr/local/etc/php-fpm.d/111-www.conf

RUN rm /usr/local/etc/php-fpm.d/zz-docker.conf && \
        mv /usr/local/etc/php-fpm.d/docker.conf /usr/local/etc/php-fpm.d/010-docker.conf && \
        mv /usr/local/etc/php-fpm.d/www.conf.default /usr/local/etc/php-fpm.d/000-www.conf.default && \
        mv /usr/local/etc/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/001-www.conf && \
        ls /usr/local/etc/php-fpm.d

RUN chown -R www-data:www-data /usr/local/etc/php-fpm.d $PHP_INI_DIR/conf.d

USER www-data

RUN php -v && composer -V

#ENTRYPOINT ["/usr/local/scripts/entrypoint.sh"]
CMD [ "/usr/local/sbin/php-fpm", "-F" ]
