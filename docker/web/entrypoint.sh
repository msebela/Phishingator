#!/bin/bash

mkdir -p ../temp
chown www-data ../temp
touch ../logs/log.log
chown www-data ../logs/log.log
chown www-data ../templates/sites-config

printenv >> /etc/environment
envsubst < /etc/apache2/sites-enabled/phishingator.conf > /etc/apache2/sites-enabled/000-default.conf
rm /etc/apache2/sites-enabled/phishingator.conf
envsubst < /etc/msmtprc.template > /etc/msmtprc
rm /etc/msmtprc.template

cron
/usr/sbin/apache2ctl -D FOREGROUND