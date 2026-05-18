# Sidecar that runs `mysqldump | gzip` every hour against the shobjiwala_db
# service and prunes its own output dir to a 24-file rolling window. Same
# MariaDB version as the DB so the client/protocol always matches.

FROM mariadb:10.11

# Cron, gzip (already on the image), and `tini` so PID 1 reaps zombies and
# forwards signals cleanly when docker stop fires.
RUN apt-get update \
    && apt-get install -y --no-install-recommends cron tini ca-certificates \
    && rm -rf /var/lib/apt/lists/*

COPY backup.sh /usr/local/bin/backup.sh
COPY crontab   /etc/cron.d/db-backup
RUN  chmod 0755 /usr/local/bin/backup.sh \
  && chmod 0644 /etc/cron.d/db-backup \
  && touch /var/log/backup.log

# Run cron in the foreground; `tini` is PID 1 so SIGTERM propagates to cron
# and any in-flight mysqldump child process.
ENTRYPOINT ["/usr/bin/tini", "--"]
CMD ["bash", "-c", "cron && tail -F /var/log/backup.log"]
