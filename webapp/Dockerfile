FROM thruio/docker-webapp

ADD . /app
RUN composer install

RUN sed -i "s/newrelic.appname.*/newrelic.appname = \"Automate Everything\"/g" /etc/php5/mods-available/newrelic.ini