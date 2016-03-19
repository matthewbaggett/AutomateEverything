FROM thruio/docker-webapp:php7

ADD . /app
RUN composer install

# Add startup scripts
RUN mkdir /etc/service/monitor
ADD run.camera-monitor.sh /etc/service/monitor/run
RUN chmod +x /etc/service/*/run

RUN sed -i "s/disable_functions.*/;disable_functions = /g" /etc/php/7.0/cli/php.ini