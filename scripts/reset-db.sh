#!/bin/bash

RETURN_CODE=1

if [ $# -ne 1 ]; then
  echo "Drops all tables and deletes Phishingator database for specific instance (by organization name)."
  echo
  echo "Usage:"
  echo "  $(basename "$0") <organization-name>"
else
  ORG=$1

  INSTANCE_DIR="/phishingator-data/$ORG"

  if [[ ! -d "$INSTANCE_DIR" ]]; then
    echo "Instance directory '$INSTANCE_DIR' does not exist!" >&2; exit 1
  fi

  read -r -p "Are you sure you want to reset Phishingator database: '$ORG' organization? [y/Y] " response

  if [[ $response =~ ^[Yy]$ ]]; then
    MESSAGE_DATETIME="$(date +"%Y-%m-%d %H:%M:%S")"
    MESSAGE=": [$(basename "$0")]  - Deleting Phishingator database for org. '$ORG'."

    if rm -rf "$INSTANCE_DIR"/database/phishingator/*; then
      LOG="$MESSAGE_DATETIME [INFO ] $MESSAGE was successful."
      RETURN_CODE=0
    else
      LOG="$MESSAGE_DATETIME [ERROR] $MESSAGE failed."
    fi

    echo "$LOG" | tee -a "$INSTANCE_DIR"/logs/log.log
  fi
fi

exit $RETURN_CODE