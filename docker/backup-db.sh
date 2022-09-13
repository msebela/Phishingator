#!/bin/bash

DB_CONTAINER=''
DB_USERNAME=''
DB_PASSWORD=''

BACKUP_DIR=''
BACKUP_FILENAME="$DB_CONTAINER-$(date +"%Y-%m-%d-%H-%M-%S.sql.gz")"

mysqldump --all-databases -h$DB_CONTAINER -u"$DB_USERNAME" -p"$DB_PASSWORD" > gzip > "$BACKUP_DIR"/"$BACKUP_FILENAME"