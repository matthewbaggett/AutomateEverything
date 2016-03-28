FROM thruio/docker-webapp:php7

RUN add-apt-repository ppa:mc3man/trusty-media && \
    apt-get update && \
    apt-get -yq install \
        livemedia-utils \
        ffmpeg \
        build-essential && \
    git clone https://github.com/nginx/nginx.git -b release-1.5.5 /tmp/nginx

RUN wget http://nginx.org/download/nginx-1.9.12.tar.gz && \
    tar -xzf nginx-1.9.12.tar.gz && \
    rm /tmp/nginx  -R && \
    mv nginx-1.9.12 /tmp/nginx

RUN cd /tmp/nginx && \
    ls -lah && \
    ./configure --prefix=/usr/local/share/nginx-rtmp --sbin-path=/usr/local/bin/nginx-rtmp \
      --conf-path=/etc/nginx-rtmp/nginx.conf --error-log-path=/var/log/nginx-rtmp/error.log \
      --http-log-path=/var/log/nginx-rtmp/access.log --pid-path=/run/nginx-rtmp.pid \
      --lock-path=/run/nginx-rtmp.lock --user=www --group=www --without-http_charset_module \
      --without-http_gzip_module --without-http_ssi_module --without-http_userid_module \
      --without-http_access_module --without-http_auth_basic_module --without-http_autoindex_module \
      --without-http_geo_module --without-http_map_module --without-http_split_clients_module \
      --without-http_referer_module --without-http_rewrite_module --without-http_proxy_module \
      --without-http_fastcgi_module --without-http_uwsgi_module --without-http_scgi_module \
      --without-http_memcached_module --without-http_limit_conn_module --without-http_limit_req_module \
      --without-http_empty_gif_module --without-http_browser_module --without-http_upstream_hash_module \
      --without-http_upstream_ip_hash_module --without-http_upstream_least_conn_module \
      --without-http_upstream_keepalive_module --without-http_upstream_zone_module \
      --add-module=modules/rtmp --add-module=modules/lua && \
    make && \
    make install && \
    rm /tmp/nginx && \
    apt-get remove build-essential && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /var/cache/apt/archives/*.deb && \

ADD . /app
RUN composer install

EXPOSE 8090

# Add startup scripts
RUN mkdir /etc/service/ffserver
ADD run.camera-monitor.sh /etc/service/monitor/run
ADD run.ffserver.sh /etc/service/ffserver/run
RUN chmod +x /etc/service/*/run

RUN sed -i "s/disable_functions.*/;disable_functions = /g" /etc/php/7.0/cli/php.ini