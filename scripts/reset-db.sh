#!/bin/bash

if [ $# -ne 1 ]; then
  echo "Drops all tables and deletes Phishingator database for specific instance (by organization name)."
  echo
  echo "Usage:"
  echo "  $(basename "$0") <organization-name>"
else
  ORG=$1
  read -r -p "Are you sure you want to reset Phishingator database: '$ORG' organization? [y/Y] " response

  if [[ $response =~ ^[Yy]$ ]]; then
    rm -rf /phishingator-data/"$ORG"/database/phishingator/*
  fi
fi