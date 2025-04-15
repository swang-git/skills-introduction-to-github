#!/bin/bash
#
# this is should put in /etc/rc.d/rc.local to prevent audit log from flowing to /var/log/message
# we are going to manually to run this after login
#
# This script will be executed *after* all the other init scripts.
# You can put your own initialization stuff in here if you don't
# want to do the full Sys V style init stuff.

# Fedora 22, massive pollution of logs
# https://bugzilla.redhat.com/show_bug.cgi?id=1227379
auditctl -e 0

sudo touch /var/lock/subsys/local