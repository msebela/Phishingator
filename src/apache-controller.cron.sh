#!/bin/bash

# CRON path
export PATH='/usr/bin:/bin:/usr/sbin/'

phishingator_sites_config='/var/www/phishingator/templates/sites-config/'
apache_sites_dir='/etc/apache2/sites-available/'

cd "$phishingator_sites_config"

i=0

for filename in *.conf.*; do
    [ -e "$filename" ] || continue

    server_name=`cat "$filename" | grep 'ServerName' | awk {'print $2'}`

    # Deaktivace podvodne stranky v Apache
    if [ "$filename" == *".conf.delete"* ]
    then
      a2dissite "$server_name"
      echo "a2dissite "$server_name""

      rm "$phishingator_sites_config/$filename"
      echo "rm \"$phishingator_sites_config/$filename\""
    fi

    # Aktivace podvodne stranky v Apache
    if [ "$filename" == *".conf.new"* ]
    then
      cp "$filename" "$apache_sites_dir/$server_name.conf"
      echo "cp \"$filename\" \"$apache_sites_dir/$server_name.conf\""

      if grep -q "<VirtualHost \*:443>" "$filename"
      then
        document_root=`cat "$filename" | grep 'DocumentRoot' | awk {'print $2'}`

        #certbot --non-interactive --register-unsafely-without-email --webroot --installer apache -w "$server_name" -d "$document_root"
        echo 'certbot --non-interactive --register-unsafely-without-email --webroot --installer apache -w "$server_name" -d "$document_root"'
      fi

      a2ensite "$server_name"
      echo "a2ensite \"$server_name\""

      mv "$phishingator_sites_config/$filename" "$phishingator_sites_config/$server_name.conf"
      echo "mv \"$phishingator_sites_config/$filename\" \"$phishingator_sites_config/$server_name.conf\""
    fi

    ((i=i+1))
    printf "\n\n"
done

# Pokud doslo k nejakym zmenam, provest reload Apache
if [ "$i" -gt 0 ]
then
  systemctl reload apache2
  echo "systemctl reload apache2"
fi