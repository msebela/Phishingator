#!/bin/bash

if [ $# -ne 2 ]; then
  echo "Restores Phishingator database for specific instance (by organization name) from selected backup file (dump)."
  echo
  echo "Usage:"
  echo "  $(basename "$0") <organization-name> <database-dump-file.sql.gz>"
else
  ORG=$1
  CONTAINER_NAME="phishingator_database_$ORG"

  BACKUP_FILE="phishingator-data/$ORG/database-dumps/$2"

  read -r -p "Are you sure you want to restore Phishingator database (org. '$ORG') from file: '$BACKUP_FILE'? [y/Y] " response

  if [[ $response =~ ^[Yy]$ ]]; then
    DB_USERNAME=$(docker exec "$CONTAINER_NAME" printenv DB_USERNAME)
    DB_PASSWORD=$(docker exec "$CONTAINER_NAME" printenv DB_PASSWORD)
    DB_DATABASE=$(docker exec "$CONTAINER_NAME" printenv DB_DATABASE)

    gunzip < "$BACKUP_FILE" | docker exec -i "$CONTAINER_NAME" /usr/bin/mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" -D"$DB_DATABASE"
  fi
fi