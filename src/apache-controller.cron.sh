#!/bin/bash

# CRON path
export PATH='/usr/bin:/bin:/usr/sbin/'

PHISHINGATOR_SITES_CONFIG='/var/www/phishingator/templates/sites-config/'
APACHE_SITES_DIR='/etc/apache2/sites-available/'

cd "$PHISHINGATOR_SITES_CONFIG"

i=0

for FILENAME in *.conf.*; do
    [ -e "$FILENAME" ] || continue

    SERVER_NAME=`cat "$FILENAME" | grep 'ServerName' | awk {'print $2'}`

    # Deactivate fraudulent website in Apache
    if [ "$FILENAME" == *".conf.delete"* ]
    then
      a2dissite "$SERVER_NAME"
      echo "a2dissite "$SERVER_NAME""

      rm "$PHISHINGATOR_SITES_CONFIG/$FILENAME"
      echo "rm \"$PHISHINGATOR_SITES_CONFIG/$FILENAME\""
    fi

    # Activate fraudulent website in Apache
    if [ "$FILENAME" == *".conf.new"* ]
    then
      cp "$FILENAME" "$APACHE_SITES_DIR/$SERVER_NAME.conf"
      echo "cp \"$FILENAME\" \"$APACHE_SITES_DIR/$SERVER_NAME.conf\""

      if grep -q "<VirtualHost \*:443>" "$FILENAME"
      then
        DOCUMENT_ROOT=`cat "$FILENAME" | grep 'DocumentRoot' | awk {'print $2'}`

        certbot --non-interactive --register-unsafely-without-email --webroot --installer apache -w "$SERVER_NAME" -d "$DOCUMENT_ROOT"
        echo 'certbot --non-interactive --register-unsafely-without-email --webroot --installer apache -w "$server_name" -d "$DOCUMENT_ROOT"'
      fi

      a2ensite "$SERVER_NAME"
      echo "a2ensite \"$SERVER_NAME\""

      mv "$PHISHINGATOR_SITES_CONFIG/$FILENAME" "$PHISHINGATOR_SITES_CONFIG/$SERVER_NAME.conf"
      echo "mv \"$PHISHINGATOR_SITES_CONFIG/$FILENAME\" \"$PHISHINGATOR_SITES_CONFIG/$SERVER_NAME.conf\""
    fi

    ((i=i+1))
    printf "\n\n"
done

# Reload Apache if something has changed
if [ "$i" -gt 0 ]
then
  systemctl reload apache2
  echo "systemctl reload apache2"
fi