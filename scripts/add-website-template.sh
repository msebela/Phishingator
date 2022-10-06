#!/bin/bash

cd ../
source .env

if [ $# == 0 ]; then
  echo "Usage:"
  echo "  $(basename "$0") \"<template-name>\" <template-host-directory>"
  echo
  echo "Example:"
  echo "  $(basename "$0") \"Fake SSO login\" /fake-sso-login-template/"
else
  CONTAINER_NAME="phishingator_database_$ORG"

  TEMPLATES_HOST_PATH="phishingator-data/$ORG/websites-templates/websites"
  COUNT_TEMPLATES=$(find "$TEMPLATES_HOST_PATH" -maxdepth 1 | wc -l)

  TEMPLATE_DIR_NAME="$((COUNT_TEMPLATES+1))-$(basename "$2")"
  TEMPLATE_PATH="/var/www/phishingator/templates/websites/$TEMPLATE_DIR_NAME"

  read -r -p "Are you sure you want to add new fraudulent website template '$1' to Phishingator ('$ORG' instance)? [y/Y] " response

  if [[ $response =~ ^[Yy]$ ]]; then
    cp -R "$2" "$TEMPLATES_HOST_PATH"/"$TEMPLATE_DIR_NAME"
    docker exec "$CONTAINER_NAME" mysql -u"$DB_USERNAME" -p"$DB_PASSWORD" -NBe "INSERT INTO phg_websites_templates (name, server_dir) VALUES ('$1', '$TEMPLATE_PATH');" "$DB_DATABASE"
  fi
fi