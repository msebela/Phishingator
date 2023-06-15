#!/bin/bash

RETURN_CODE=1

if [ $# -ne 4 ]; then
  echo "Adds new fraudulent website template for specific Phishingator instance (by organization name)."
  echo
  echo "Usage:"
  echo "  $(basename "$0") <organization-name> \"<template-name>\" <template-directory> <cloned-page>"
  echo
  echo "Examples:"
  echo "  $(basename "$0") cesnet \"SSO login\" /sso-login-template/ 1"
  echo "  $(basename "$0") cesnet \"Fake SSO login\" /fake-sso-login-template/ 0"
else
  ORG=$1

  CONTAINER_NAME="phishingator-$ORG-database"

  INSTANCE_DIR="/phishingator-data/$ORG"

  if [ "$(docker container inspect -f '{{.State.Status}}' "$CONTAINER_NAME")" != "running" ]; then
    echo "Phishingator database container '$CONTAINER_NAME' is not running." >&2; exit 1
  fi

  if [[ ! -d "$3" ]]; then
    echo "Template directory '$3' does not exist!" >&2; exit 1
  fi

  if ! [[ $4 =~ ^[0|1]$ ]]; then
    echo "Cloned page argument can only be set to 1 (copied login page) or 0 (fake login page)." >&2; exit 1
  fi

  TEMPLATES_HOST_PATH="$INSTANCE_DIR/websites-templates/websites"
  COUNT_TEMPLATES=$(find "$TEMPLATES_HOST_PATH" -maxdepth 1 | wc -l)

  TEMPLATE_DIR_NAME="$((COUNT_TEMPLATES))-$(basename "$3")"
  TEMPLATE_PATH="/var/www/phishingator/templates/websites/$TEMPLATE_DIR_NAME"

  read -r -p "Are you sure you want to add new fraudulent website template '$2' to Phishingator (org. '$ORG') from dir: '$3'? [y/Y] " response

  if [[ $response =~ ^[Yy]$ ]]; then
    DB_USERNAME=$(docker exec "$CONTAINER_NAME" printenv MYSQL_USER)
    DB_PASSWORD=$(docker exec "$CONTAINER_NAME" printenv MYSQL_ROOT_PASSWORD)
    DB_DATABASE=$(docker exec "$CONTAINER_NAME" printenv MYSQL_DATABASE)

    MESSAGE_DATETIME="$(date +"%Y-%m-%d %H:%M:%S")"
    MESSAGE=": [$(basename "$0")]  - Adding new fraudulent website template to Phishingator database for org. '$ORG'"

    cp -R "$3" "$TEMPLATES_HOST_PATH"/"$TEMPLATE_DIR_NAME"

    if docker exec "$CONTAINER_NAME" /usr/bin/mariadb -u"$DB_USERNAME" -p"$DB_PASSWORD" -NBe "INSERT INTO phg_websites_templates (name, server_dir, cloned) VALUES ('$2', '$TEMPLATE_PATH/', '$4');" "$DB_DATABASE"; then
      LOG="$MESSAGE_DATETIME [INFO ] $MESSAGE was successful."
      RETURN_CODE=0
    else
      LOG="$MESSAGE_DATETIME [ERROR] $MESSAGE failed."
    fi

    echo "$LOG" | tee -a "$INSTANCE_DIR"/logs/log.log
  fi
fi

exit $RETURN_CODE