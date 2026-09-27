#!/bin/bash

[[ "$1" == "-h" ]] && echo "Usage: 'compupd', if if there is new update for devx it will continue to update prod" && exit

HOME_DIR="/Users/swang"
SITE_DIR="/Users/swang/sites"
OS=$(uname -s)
if [ "$OS" == "Linux" ]; then
    HOME_DIR="/home/swang"
    SITE_DIR="/sites"
fi

logFile=$HOME_DIR/tmp/composer-upd-devx.log
cd $SITE_DIR/devx
composer update 2>&1 | tee $logFile
if cat "$logFile" | grep -qi "Nothing to install"; then
    echo "ℹ️ No updates, skip install/build"
    exit 0
elif cat "$logFile" | grep -qi "download"; then
    logFile=$HOME_DIR/tmp/composer-upd-prod.log
    cd $SITE_DIR/prod
    composer update 2>&1 | tee $logFile
    cat "$logFile"
    ret=${PIPESTATUS[0]}
    exit $ret
else
    echo "❌ Updade error detected"
    cat "$logFile"
    exit 1
fi
