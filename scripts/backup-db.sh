#!/bin/bash

RETURN_CODE=1

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

  MESSAGE_DATETIME="$(date +"%Y-%m-%d %H-%M-%S")"
  MESSAGE=": [$(basename "$0")]  - Backup file (dump) of Phishingator database for org. '$ORG'"

  if zgrep -q "INSERT INTO \`phg_websites_templates\`" "$BACKUP_DIR"/"$BACKUP_FILENAME"; then
    echo "$MESSAGE_DATETIME [INFO ] $MESSAGE was successfully created."
    RETURN_CODE=0
  else
    echo "$MESSAGE_DATETIME [ERROR] $MESSAGE failed."
  fi
fi

exit $RETURN_CODE