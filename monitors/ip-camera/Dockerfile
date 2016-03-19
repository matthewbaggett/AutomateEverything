FROM thruio/docker-webapp:php7

RUN add-apt-repository ppa:mc3man/trusty-media && \
    apt-get update && \
    apt-get -yq install \
        livemedia-utils \
        ffmpeg && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /var/cache/apt/archives/*.deb

ADD . /app
RUN composer install

# Add startup scripts
RUN mkdir /etc/service/monitor
ADD run.camera-monitor.sh /etc/service/monitor/run
RUN chmod +x /etc/service/*/run

RUN sed -i "s/disable_functions.*/;disable_functions = /g" /etc/php/7.0/cli/php.ini