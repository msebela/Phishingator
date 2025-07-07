#!/bin/bash

set -e

# TODO: Process the CSV containing the list of users into an LDIF file here (e.g. using a Python script), or run ldapsearch to export LDAP data to local.ldif

mv local.ldif ldap/users.ldif