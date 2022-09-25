FROM php:7.4-apache

ARG APACHE_DOCUMENT_ROOT

# PHP
RUN docker-php-ext-install pdo_mysql mysqli
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions ldap imap

# Cron
RUN apt-get update && apt-get -y install cron
COPY docker/web/phishingator-cron /etc/cron.d/phishingator-cron
RUN chmod 0644 /etc/cron.d/phishingator-cron
RUN crontab /etc/cron.d/phishingator-cron
RUN cron

# Shibboleth
RUN apt-get -y install libapache2-mod-shib

# Apache
COPY docker/web/phishingator.conf /etc/apache2/sites-enabled/000-default.conf
COPY docker/web/security.conf /etc/apache2/conf-enabled/
RUN a2enmod rewrite

# LDAP
COPY docker/web/ldap.conf /etc/ldap

# MSMTPRC
COPY docker/web/mail.ini /usr/local/etc/php/conf.d/mail.ini
COPY docker/web/msmtprc /etc/msmtprc

# Phishingator
WORKDIR $APACHE_DOCUMENT_ROOT

COPY src ../
RUN chown -R www-data:www-data .

RUN touch ../logs/log.log && chmod 777 ../logs/log.log
RUN mkdir ../temp && chmod 777 ../temp
RUN chmod 777 ../templates/sites-config
RUN chmod +x ../verify-credentials.kerberos.sh
RUN chmod +x ../apache-controller.cron.sh