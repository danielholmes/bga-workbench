# Use last Composer image running PHP 7
FROM composer@sha256:d374b2e1f715621e9d9929575d6b35b11cf4a6dc237d4a08f2e6d1611f534675

RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli pdo pdo_mysql

RUN composer require --dev dholmes/bga-workbench

ENTRYPOINT ["/app/vendor/bin/bgawb"]
