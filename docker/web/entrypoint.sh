#!/bin/bash

mkdir -p ../temp
chown phishingator ../temp

chown phishingator ../logs
touch ../logs/log.log
chown phishingator ../logs/log.log
chown phishingator ../templates/sites-config

printenv >> /etc/environment
envsubst < /etc/apache2/sites-enabled/phishingator.conf > /etc/apache2/sites-enabled/000-default.conf
rm /etc/apache2/sites-enabled/phishingator.conf
envsubst < /etc/msmtprc.template > /etc/msmtprc
rm /etc/msmtprc.template

cron
/usr/sbin/apache2ctl -D FOREGROUND