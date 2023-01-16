#!/bin/bash

if [ $# -ne 3 ]; then
  echo "Adds new fraudulent website template for specific Phishingator instance (by organization name)."
  echo
  echo "Usage:"
  echo "  $(basename "$0") <organization-name> \"<template-name>\" <template-directory>"
  echo
  echo "Example:"
  echo "  $(basename "$0") cesnet \"Fake SSO login\" /fake-sso-login-template/"
else
  ORG=$1
  CONTAINER_NAME="phishingator_database_$ORG"

  TEMPLATES_HOST_PATH="phishingator-data/$ORG/websites-templates/websites"
  COUNT_TEMPLATES=$(find "$TEMPLATES_HOST_PATH" -maxdepth 1 | wc -l)

  TEMPLATE_DIR_NAME="$((COUNT_TEMPLATES+1))-$(basename "$3")"
  TEMPLATE_PATH="/var/www/phishingator/templates/websites/$TEMPLATE_DIR_NAME"

  read -r -p "Are you sure you want to add new fraudulent website template '$2' to Phishingator (org. '$ORG') from dir: '$3'? [y/Y] " response

  if [[ $response =~ ^[Yy]$ ]]; then
    DB_USERNAME=$(docker exec "$CONTAINER_NAME" printenv DB_USERNAME)
    DB_PASSWORD=$(docker exec "$CONTAINER_NAME" printenv DB_PASSWORD)
    DB_DATABASE=$(docker exec "$CONTAINER_NAME" printenv DB_DATABASE)

    cp -R "$3" "$TEMPLATES_HOST_PATH"/"$TEMPLATE_DIR_NAME"

    docker exec "$CONTAINER_NAME" mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" -NBe "INSERT INTO phg_websites_templates (name, server_dir) VALUES ('$2', '$TEMPLATE_PATH');" "$DB_DATABASE"
  fi
fi