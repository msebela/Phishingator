FROM alpine:latest

ENV TZ="Europe/Prague"

RUN apk add --no-cache curl tini
COPY docker/scheduler/phishingator-cron /etc/crontabs/root

ENTRYPOINT ["/sbin/tini", "--"]
CMD ["crond", "-f"]