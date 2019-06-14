#!/bin/bash

if [ -d /docker/ssh ]; then
    cp /docker/ssh/* /home/dev/.ssh
    chown dev:dev /home/dev/.ssh -R
    chmod 0600 /home/dev/.ssh -R
    chmod 0700 /home/dev/.ssh

    cp /docker/ssh/* /root/.ssh
    chown root:root /root/.ssh -R
    chmod 0600 /root/.ssh -R
    chmod 0700 /root/.ssh
fi
