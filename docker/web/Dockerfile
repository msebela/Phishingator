FROM php:7.4-apache

ENV TZ="Europe/Prague"
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
RUN touch /var/log/cron.log

# OIDC
RUN apt-get -y install libapache2-mod-auth-openidc

# Apache
COPY docker/web/phishingator.conf /etc/apache2/sites-enabled/phishingator.conf
COPY docker/web/security.conf /etc/apache2/conf-enabled/

RUN apt-get -y install gettext
COPY docker/web/set-envs.sh /set-envs.sh
RUN chmod +x /set-envs.sh

RUN a2enmod rewrite

# Certbot
RUN apt-get -y install certbot

# SSMTP
RUN apt-get -y install ssmtp
COPY docker/web/ssmtp.conf /etc/ssmtp/ssmtp.conf

# LDAP
COPY docker/web/ldap.conf /etc/ldap

# MSMTPRC
RUN apt-get -y install msmtp
COPY docker/web/mail.ini /usr/local/etc/php/conf.d/mail.ini
COPY docker/web/msmtprc /etc/msmtprc

# Phishingator
WORKDIR $APACHE_DOCUMENT_ROOT

COPY src ../
RUN chown -R www-data:www-data .
RUN mkdir ../temp && chown www-data ../temp

CMD /set-envs.sh && cron && /usr/sbin/apache2ctl -D FOREGROUND