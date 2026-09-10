#!/bin/bash

logFile="/Users/swang/tmp/qupd-arts.log"
if [ -f "$logFile" ]; then
    echo "$logFile exists -- which already upgraded, exit..."
else
    cd /Users/swang/sites/projects/arts
    quasar upgrade -i |tee  ~/tmp/qupd-arts.log 2>&1
fi

logFile="/Users/swang/tmp/qupd-yali.log"
if [ -f "$logFile" ]; then
    echo "$logFile exists -- which already upgraded, exit..."
else
    cd /Users/swang/sites/projects/yali
    quasar upgrade -i |tee  ~/tmp/qupd-yali.log 2>&1
fi

logFile="/Users/swang/tmp/qupd-golf.log"
if [ -f "$logFile" ]; then
    echo "$logFile exists -- which already upgraded, exit..."
else
    cd /Users/swang/sites/projects/golf
    quasar upgrade -i |tee  ~/tmp/qupd-golf.log 2>&1
fi

logFile="/Users/swang/tmp/qupd-apps.log"
if [ -f "$logFile" ]; then
    echo "$logFile exists -- which already upgraded, exit..."
else
    cd /Users/swang/sites/projects/apps
    quasar upgrade -i |tee  ~/tmp/qupd-apps.log 2>&1
fi