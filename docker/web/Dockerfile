FROM php:8.2-apache

ENV TZ="Europe/Prague"
ENV APACHE_DOCUMENT_ROOT="/var/www/phishingator/public/"
ARG SMTP_HOST

RUN apt-get update && apt-get install -y sudo && \
    useradd -u 5000 phishingator && \
    echo 'phishingator ALL=(root) NOPASSWD: /usr/sbin/a2ensite, /usr/sbin/a2dissite' >> /etc/sudoers.d/phishingator

# PHP
RUN docker-php-ext-install pdo_mysql mysqli
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions ldap imap zip

# OIDC
RUN apt-get update && apt-get install -y libapache2-mod-auth-openidc

# Apache
COPY docker/web/phishingator.conf /etc/apache2/sites-enabled/000-default.conf
COPY docker/web/security.conf /etc/apache2/conf-enabled/
RUN chown -R phishingator /etc/apache2/sites-available/ /etc/apache2/sites-enabled/ /var/lib/apache2/site/ && \
    a2enmod rewrite headers

# LDAP
COPY docker/web/ldap.conf /etc/ldap

# MSMTP
RUN apt-get install -y msmtp
COPY docker/web/mail.ini /usr/local/etc/php/conf.d/mail.ini
COPY docker/web/msmtprc /etc/msmtprc
RUN sed -i "s/\${SMTP_HOST}/${SMTP_HOST}/g" /etc/msmtprc && \
    touch /var/log/msmtp.log && \
    chown phishingator /var/log/msmtp.log

# Phishingator
WORKDIR $APACHE_DOCUMENT_ROOT
COPY src ../
RUN chown -R phishingator ../

USER phishingator