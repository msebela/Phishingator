#!/bin/bash

cd ../
source .env

if [ $# == 0 ]; then
  echo "Usage:"
  echo "  $(basename $0) database-file.sql.gz"
  echo
fi

CONTAINER_NAME="phishingator_database_$ORG"

BACKUP_FILE="phishingator-data/$ORG/database-dumps/$1"

read -r -p "Are you sure you want to restore Phishingator database from file: '$BACKUP_FILE'? [y/Y] " response

if [[ $response =~ ^[Yy]$ ]]
then
  gunzip < "$BACKUP_FILE" | docker exec -i "phishingator_database_$ORG" /usr/bin/mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE"
fi