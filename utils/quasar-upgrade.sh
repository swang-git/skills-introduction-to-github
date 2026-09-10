#!/bin/bash

[[ "$1" == "-h" ]] && echo "Usage: qupd check for checking quasar upgrade arts only; upd update all" && exit

HOME_DIR="/Users/swang"
PROJECTS_DIR="/Users/swang/sites/projects"
OS=$(uname -s)
if [ "$OS" == "Linux" ]; then
    HOME_DIR="/home/swang"
    PROJECTS_DIR="/sites/projects"
fi

logFile="$HOME_DIR/tmp/qupd-arts.log"
if [[ "$1" == "check" ]]; then
    cd $PROJECTS_DIR/arts
    echo "  ✅ -- Upgrading $PROJECTS_DIR/arts"
    quasar upgrade -i | tee $logFile 2>&1
fi

logFile="$HOME_DIR/tmp/qupd-yali.log"
if [ "$1" == "upd" ]; then
    cd $PROJECTS_DIR/yali
    echo "  ✅ -- Upgrading $PROJECTS_DIR/yali"
    quasar upgrade -i |tee $logFile 2>&1
elif [ -f "$logFile" ]; then
    echo "$logFile exists -- which already upgraded, exit..."
fi

logFile="$HOME_DIR/tmp/qupd-golf.log"
if [ "$1" == "upd" ]; then
    cd $PROJECTS_DIR/golf
    echo "  ✅ -- Upgrading $PROJECTS_DIR/golf"
    quasar upgrade -i |tee $logFile 2>&1
elif [ -f "$logFile" ]; then
    echo "$logFile exists -- which already upgraded, exit..."
fi

logFile="$HOME_DIR/tmp/qupd-apps.log"
if [ "$1" == "upd" ]; then
    cd $PROJECTS_DIR/apps
    echo "  ✅ -- Upgrading $PROJECTS_DIR/apps"
    quasar upgrade -i |tee $logFile 2>&1
elif [ -f "$logFile" ]; then
    echo "$logFile exists -- which already upgraded, exit..."
fi