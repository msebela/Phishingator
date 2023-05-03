FROM alpine:latest

ENV TZ="Europe/Prague"

RUN apk --no-cache add curl
COPY docker/scheduler/phishingator-cron /etc/crontabs/root

CMD ["crond", "-f"]