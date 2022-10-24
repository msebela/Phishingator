#!/bin/bash

printenv >> /etc/environment

touch $APACHE_DOCUMENT_ROOT/../logs/log.log
chown www-data $APACHE_DOCUMENT_ROOT/../logs/log.log
chown www-data $APACHE_DOCUMENT_ROOT/../templates/sites-config

envsubst < /etc/apache2/sites-enabled/phishingator.conf > /etc/apache2/sites-enabled/000-default.conf
rm /etc/apache2/sites-enabled/phishingator.conf

envsubst < /etc/msmtprc.template > /etc/msmtprc
rm /etc/msmtprc.template