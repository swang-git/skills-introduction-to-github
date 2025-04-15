#!/usr/bin/bash

/usr/bin/systemctl stop mythbackend   2>&1 | tee /tmp/start_mythbackend.log
/usr/bin/systemctl start mythbackend  2>&1 | tee /tmp/stop_mythbackend.log