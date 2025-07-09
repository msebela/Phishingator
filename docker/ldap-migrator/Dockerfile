FROM python:3.12-slim

ENV LDAPTLS_CACERT="/etc/ssl/certs/ldap.crt" \
    LDAPTLS_REQCERT="demand"

RUN apt-get update && \
    apt-get install -y ldap-utils && \
    rm -rf /var/lib/apt/lists/*

WORKDIR /phishingator

COPY docker/ldap-migrator/entrypoint.sh entrypoint.sh
RUN chmod +x entrypoint.sh

RUN mkdir ldap

ENTRYPOINT ["./entrypoint.sh"]