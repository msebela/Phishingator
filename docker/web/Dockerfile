FROM php:8.2-apache

ENV TZ="Europe/Prague"
ARG APACHE_DOCUMENT_ROOT

RUN apt-get update && apt-get install -y && \
    useradd -u 5000 phishingator

# PHP
RUN docker-php-ext-install pdo_mysql mysqli
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions ldap imap zip

# Cron
RUN apt-get update && apt-get -y install cron
COPY docker/web/phishingator-cron /etc/cron.d/phishingator-cron
RUN chmod u+s /usr/sbin/cron && \
    chown root:crontab /usr/bin/crontab && \
    chmod 2755 /usr/bin/crontab && \
    chown phishingator:crontab /var/spool/cron/crontabs/phishingator && \
    chmod 600 /var/spool/cron/crontabs/phishingator && \
    crontab -u phishingator /etc/cron.d/phishingator-cron && \
    touch /var/log/cron.log

# OIDC
RUN apt-get -y install libapache2-mod-auth-openidc

# Apache
COPY docker/web/phishingator.conf /etc/apache2/sites-enabled/000-default.conf
COPY docker/web/security.conf /etc/apache2/conf-enabled/

RUN a2enmod rewrite

# LDAP
COPY docker/web/ldap.conf /etc/ldap

# MSMTP
RUN apt-get -y install msmtp
COPY docker/web/mail.ini /usr/local/etc/php/conf.d/mail.ini
COPY docker/web/msmtprc /etc/msmtprc

# Phishingator
WORKDIR $APACHE_DOCUMENT_ROOT
COPY src ../
RUN chown -R phishingator ../

RUN sed -i 's/^exec /service cron start\n\nexec /' /usr/local/bin/apache2-foreground

USER phishingator:phishingator