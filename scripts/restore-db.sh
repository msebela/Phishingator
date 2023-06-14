#!/bin/bash

if [ $# -ne 2 ]; then
  echo "Restores Phishingator database for specific instance (by organization name) from selected backup file (dump)."
  echo
  echo "Usage:"
  echo "  $(basename "$0") <organization-name> <database-dump-file.sql.gz>"
else
  ORG=$1
  CONTAINER_NAME="phishingator-$ORG-database"

  BACKUP_FILE="/phishingator-data/$ORG/database-dumps/$2"

  if [ "$(docker container inspect -f '{{.State.Status}}' "$CONTAINER_NAME")" != "running" ]; then
    echo "Phishingator database container '$CONTAINER_NAME' is not running." >&2; exit 1
  fi

  if [[ ! -f $BACKUP_FILE ]]; then
    echo "Backup file '$BACKUP_FILE' does not exist." >&2; exit 1
  fi

  read -r -p "Are you sure you want to restore Phishingator database (org. '$ORG') from file: '$BACKUP_FILE'? [y/Y] " response

  if [[ $response =~ ^[Yy]$ ]]; then
    DB_USERNAME=$(docker exec "$CONTAINER_NAME" printenv MYSQL_USER)
    DB_PASSWORD=$(docker exec "$CONTAINER_NAME" printenv MYSQL_ROOT_PASSWORD)
    DB_DATABASE=$(docker exec "$CONTAINER_NAME" printenv MYSQL_DATABASE)

    gunzip < "$BACKUP_FILE" | docker exec -i "$CONTAINER_NAME" /usr/bin/mariadb -u"$DB_USERNAME" -p"$DB_PASSWORD" -D"$DB_DATABASE"
  fi
fi