FROM python:alpine as build

RUN apk add openldap-clients
WORKDIR /build
COPY docker/ldap .

# TODO: Process CSV containing the list of users into LDIF here (e.g. using Python script) or run ldapsearch and copy LDIF it into new LDAP container


FROM osixia/openldap:latest

ENV TZ="Europe/Prague"

COPY --from=build /build/local.ldif /container/service/slapd/assets/config/bootstrap/ldif/custom/bootstrap.ldif