#!/bin/bash

read -r -p "Opravdu chcece odstranit data z databaze Phishingatoru a znovu Phishingator spustit? [y/Y] " response

if [[ $response =~ ^[Yy]$ ]]
then
  rm -rf data-db
  docker-compose up --force-recreate
fi