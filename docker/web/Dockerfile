FROM php:8.3-apache-bookworm

ENV TZ="Europe/Prague" \
    APACHE_DOCUMENT_ROOT="/var/www/phishingator/public/" \
    LDAPTLS_CACERT="/etc/ssl/certs/ldap.crt" \
    LDAPTLS_REQCERT="demand"
ARG SMTP_HOST

RUN apt-get update && \
    apt-get install -y sudo libapache2-mod-auth-openidc msmtp vim && \
    rm -rf /var/lib/apt/lists/*

RUN useradd -u 5000 phishingator

# PHP
RUN curl -sSLf \
        -o /usr/local/bin/install-php-extensions \
        https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions && \
    chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pdo_mysql ldap imap zip

# Apache
COPY docker/web/phishingator.conf /etc/apache2/sites-enabled/000-default.conf
COPY docker/web/security.conf /etc/apache2/conf-enabled/
RUN chown -R phishingator /etc/apache2/sites-available/ /etc/apache2/sites-enabled/ /var/lib/apache2/site/ && \
    a2enmod rewrite headers

# MSMTP
COPY docker/web/mail.ini /usr/local/etc/php/conf.d/mail.ini
COPY docker/web/msmtprc /etc/msmtprc
RUN sed -i "s/\${SMTP_HOST}/${SMTP_HOST}/g" /etc/msmtprc && \
    touch /var/log/msmtp.log && \
    chown phishingator /var/log/msmtp.log

# Phishingator
WORKDIR $APACHE_DOCUMENT_ROOT
COPY --chown=phishingator:phishingator src ../

USER phishingator