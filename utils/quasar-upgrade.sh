#!/bin/bash

[[ "$1" == "-h" ]] && echo "Usage: qupd check for checking quasar upgrade arts only; upd update all" && exit

logFile="/Users/swang/tmp/qupd-arts.log"
if [[ "$1" == "check" ]]; then
    cd ~/sites/projects/arts
    echo "  ✅ -- Upgrading arts"
    quasar upgrade -i | tee $logFile 2>&1
fi

logFile="/Users/swang/tmp/qupd-yali.log"
if [ "$1" == "upd" ]; then
    cd /Users/swang/sites/projects/yali
    echo "  ✅ -- Upgrading yali"
    quasar upgrade -i |tee $logFile 2>&1
elif [ -f "$logFile" ]; then
    echo "$logFile exists -- which already upgraded, exit..."
fi

logFile="/Users/swang/tmp/qupd-golf.log"
if [ "$1" == "upd" ]; then
    cd /Users/swang/sites/projects/golf
    echo "  ✅ -- Upgrading golf"
    quasar upgrade -i |tee $logFile 2>&1
elif [ -f "$logFile" ]; then
    echo "$logFile exists -- which already upgraded, exit..."
fi

logFile="/Users/swang/tmp/qupd-apps.log"
if [ "$1" == "upd" ]; then
    cd /Users/swang/sites/projects/apps
    echo "  ✅ -- Upgrading apps"
    quasar upgrade -i |tee $logFile 2>&1
elif [ -f "$logFile" ]; then
    echo "$logFile exists -- which already upgraded, exit..."
fi