#!/bin/bash

if [ $# -ne 1 ]; then
  echo "Creates backup file (dump) Phishingator database for specific instance (by organization name)."
  echo
  echo "Usage:"
  echo "  $(basename "$0") <organization-name>"
else
  ORG=$1
  CONTAINER_NAME="phishingator_database_$ORG"

  BACKUP_DIR="phishingator-data/$ORG/database-dumps"
  BACKUP_FILENAME="$(date +"%Y-%m-%d-%H-%M-%S.sql.gz")"

  DB_USERNAME=$(docker exec "$CONTAINER_NAME" printenv DB_USERNAME)
  DB_PASSWORD=$(docker exec "$CONTAINER_NAME" printenv DB_PASSWORD)
  DB_DATABASE=$(docker exec "$CONTAINER_NAME" printenv DB_DATABASE)

  docker exec "$CONTAINER_NAME" /usr/bin/mysqldump -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" -B | gzip > "$BACKUP_DIR"/"$BACKUP_FILENAME"
fi