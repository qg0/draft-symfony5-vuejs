FROM library/php:fpm-alpine
RUN apk update; apk upgrade; apk add bash openssh git composer npm mc icu icu-dev;
RUN docker-php-ext-install mysqli pdo pdo_mysql intl
RUN curl -sL https://phar.phpunit.de/phpunit-9.phar > /usr/local/bin/phpunit && chmod +x /usr/local/bin/phpunit
WORKDIR /var/www
