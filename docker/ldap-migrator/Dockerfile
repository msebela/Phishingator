FROM python:3.12-alpine

ENV LDAPTLS_CACERT="/etc/ssl/certs/ldap.crt" \
    LDAPTLS_REQCERT="demand"

RUN apk add --no-cache bash openldap-clients

WORKDIR /phishingator

COPY docker/ldap-migrator/entrypoint.sh entrypoint.sh
RUN chmod +x entrypoint.sh

RUN mkdir ldap

ENTRYPOINT ["./entrypoint.sh"]