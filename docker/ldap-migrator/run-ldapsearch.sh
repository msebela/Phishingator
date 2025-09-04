#!/bin/bash

set -e

LDAPSEARCH_ARGS=(
  -E pr=1000/noprompt
  -H "$LDAP_MIGRATOR_HOST"
  -b "$LDAP_MIGRATOR_BASE_DN"
  -LL
  -D "$LDAP_MIGRATOR_USER"
  -w "$LDAP_MIGRATOR_PASS"
)

LDAPSEARCH_FILTER="(&(mail=*)(objectClass=organizationalPerson)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))"
LDAPSEARCH_ATTRS=(objectClass uid givenName sn mail memberOf)

ldapsearch "${LDAPSEARCH_ARGS[@]}" "${LDAPSEARCH_FILTER}" "${LDAPSEARCH_ATTRS[@]}" > ldap/data/users.ldif

# TODO: If you want to use LDAP data as input, replace the filter and attributes above with your own ldapsearch command(s)