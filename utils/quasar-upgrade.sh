#!/bin/bash

[[ $1 == "-h" ]] && echo "Usage: qupd check for checking quasar upgrade arts only" && exit

logFile="/Users/swang/tmp/qupd-arts.log"
if [[ "$1" == "check" ]]; then
    cd ~/sites/projects/arts
    quasar upgrade -i | tee $logFile 2>&1
fi

if [ -f "$logFile" ]; then
    echo "$logFile exists -- which already upgraded, exit..."
else
    cd /Users/swang/sites/projects/arts
    quasar upgrade -i |tee $logFile 2>&1
fi

logFile="/Users/swang/tmp/qupd-yali.log"
if [ -f "$logFile" ]; then
    echo "$logFile exists -- which already upgraded, exit..."
else
    cd /Users/swang/sites/projects/yali
    quasar upgrade -i |tee $logFile 2>&1
fi

logFile="/Users/swang/tmp/qupd-golf.log"
if [ -f "$logFile" ]; then
    echo "$logFile exists -- which already upgraded, exit..."
else
    cd /Users/swang/sites/projects/golf
    quasar upgrade -i |tee $logFile 2>&1
fi

logFile="/Users/swang/tmp/qupd-apps.log"
if [ -f "$logFile" ]; then
    echo "$logFile exists -- which already upgraded, exit..."
else
    cd /Users/swang/sites/projects/apps
    quasar upgrade -i |tee $logFile 2>&1
fi