FROM alpine

ARG NGINX_VERSION=1.20.1

ADD nginx.conf /etc/nginx/
ADD setenv.sh /tmp/
ADD setenv.inc.sh /tmp/
ADD helpers.sh /tmp/
ADD logrotate.sh /
ADD entrypoint.sh /

RUN apk add --no-cache g++ make pcre-dev zlib-dev bash shadow && \
    wget https://github.com/openresty/headers-more-nginx-module/archive/v0.33.tar.gz && \
    tar -xzvf v0.33.tar.gz && \
    wget http://nginx.org/download/nginx-${NGINX_VERSION}.tar.gz && \
    tar -xzvf nginx-${NGINX_VERSION}.tar.gz && \
    cd nginx-${NGINX_VERSION} && \
    ./configure --sbin-path=/usr/sbin/nginx --conf-path=/etc/nginx/nginx.conf --error-log-path=/var/log/nginx/error.log --http-log-path=/var/log/nginx/access.log --user=www-data --group=www-data --modules-path=/usr/lib/nginx/modules --http-client-body-temp-path=/var/lib/nginx/body --add-module=/headers-more-nginx-module-0.33 --with-http_realip_module --with-http_secure_link_module --with-http_stub_status_module && \
    make && \
    make install && \
    mkdir -p /etc/nginx/conf.d && \
    mkdir -p /var/lib/nginx/body && \
    mkdir -p /usr/local/nginx/proxy_temp && \
    chmod +x /tmp/setenv.sh && \
    adduser -S www-data -G www-data && usermod -u 1000 www-data && \
    ln -sf /dev/stdout /tmp/nginx.stdout  && \
    ln -sf /dev/stderr /tmp/nginx.stderr && \
    touch /var/log/nginx/access.log && \
    touch /var/log/nginx/error.log && \
    chmod +x /logrotate.sh && \
    chmod +x /entrypoint.sh && \
    echo "0 0 * * * /bin/bash /logrotate.sh" > /etc/crontabs/root

EXPOSE 80 443

CMD ["/bin/bash", "-c", "/entrypoint.sh"]