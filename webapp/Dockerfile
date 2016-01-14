FROM phusion/baseimage:latest
MAINTAINER Matthew Baggett <matthew@baggett.me>

CMD ["/sbin/my_init"]

# Install base packages
RUN apt-get update && \
    apt-get -yq install \
        nano \
        curl \
        git \
        memcached \
        mysql-client \
        apache2 \
        libapache2-mod-php5 \
        php5-mysql \
        php5-gd \
        php5-curl \
	    php5-memcached \
        php-apc && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN sed -i "s/variables_order.*/variables_order = \"EGPCS\"/g" /etc/php5/apache2/php.ini
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Configure /app folder with sample app
RUN mkdir -p /app && rm -fr /var/www/html && ln -s /app /var/www/html
ADD . /app
ADD docker/ApacheConfig.conf /etc/apache2/sites-enabled/000-default.conf

# Run Composer
RUN cd /app && composer install

# Enable mod_rewrite
RUN a2enmod rewrite && /etc/init.d/apache2 restart

# Add ports.
EXPOSE 80

WORKDIR /app

# Add configs
ADD docker/apache2.conf /etc/apache2/apache2.conf

# Add startup scripts
RUN mkdir /etc/service/apache2
ADD docker/run.apache.sh /etc/service/apache2/run
RUN chmod +x /etc/service/*/run
