FROM python:alpine as build

RUN apk add openldap-clients
WORKDIR /build
COPY docker/ldap .

# 1) TODO: Process CSV to LDIF here (e.g. via Python script)

FROM osixia/openldap:latest

ENV TZ="Europe/Prague"

# 2) TODO: Copy LDIF into LDAP container (via COPY command)
# COPY --from=build /build/local.ldif /container/service/slapd/assets/config/bootstrap/ldif/custom/bootstrap.ldif