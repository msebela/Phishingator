#!/bin/bash

cd ../
source .env

CONTAINER_NAME="phishingator_database_$ORG"

BACKUP_DIR="phishingator-data/$ORG/database-dumps"
BACKUP_FILENAME="$(date +"%Y-%m-%d-%H-%M-%S.sql.gz")"

docker exec "phishingator_database_$ORG" /usr/bin/mysqldump -u"$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" | gzip > "$BACKUP_DIR"/"$BACKUP_FILENAME"