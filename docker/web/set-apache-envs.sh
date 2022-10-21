#!/bin/bash

touch /var/www/phishingator/logs/log.log
chmod 777 /var/www/phishingator/logs/log.log

export
envsubst < /etc/apache2/sites-enabled/phishingator.conf > /etc/apache2/sites-enabled/000-default.conf
rm /etc/apache2/sites-enabled/phishingator.conf