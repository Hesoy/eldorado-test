#!/bin/bash

if [ -f /docker/crontab ]; then
    cp /docker/crontab /etc/cron.d/gepard-cron
    chown root:root /etc/cron.d/gepard-cron
    chmod 0644 /etc/cron.d/gepard-cron
fi
