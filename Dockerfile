FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends default-mysql-client \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && a2enmod rewrite

COPY docker/php.ini /usr/local/etc/php/conf.d/cmms-render.ini
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY docker/render-start.sh /usr/local/bin/render-start.sh
RUN chmod +x /usr/local/bin/render-start.sh

COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html

CMD ["render-start.sh"]
