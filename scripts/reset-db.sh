#!/bin/bash

cd ../
source .env

read -r -p "Are you sure you want to reset Phishingator ($ORG) database? [y/Y] " response

if [[ $response =~ ^[Yy]$ ]]; then
  rm -rf phishingator-data/"$ORG"/database
  docker-compose up --force-recreate
fi