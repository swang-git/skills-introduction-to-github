#!/bin/bash

[[ "$1" == "-h" ]] && echo "Usage: 'qupd', if if there is new update for art it will continue to upgrade for yali/golf/apps" && exit

HOME_DIR="/Users/swang"
PROJECTS_DIR="/Users/swang/sites/projects"
OS=$(uname -s)
if [ "$OS" == "Linux" ]; then
    HOME_DIR="/home/swang"
    PROJECTS_DIR="/sites/projects"
fi

logFile="$HOME_DIR/tmp/qupd-arts.log"
cd $PROJECTS_DIR/arts
quasar upgrade 2>&1 | tee $logFile
if cat "$logFile" | grep -qi "Congrats"; then
    echo "ℹ️ Already latest version, skip install/build"
    exit 0
elif cat "$logFile" | grep -qi "install"; then
    # echo "$output"
    echo "✅ Package arts upgraded"
else
    echo "❌ Upgrade error detected:"
    echo "$logFile"
    exit 1
fi
logFile="$HOME_DIR/tmp/qupd-yali.log"
cd $PROJECTS_DIR/yali
quasar upgrade 2>&1 | tee $logFile
if cat "$logFile" | grep -qi "Congrats"; then
    echo "ℹ️ Already latest version, skip install/build"
    exit 0
elif cat "$logFile" | grep -qi "install"; then
    # echo "$output"
    echo "✅ Package arts upgraded"
else
    echo "❌ Upgrade error detected:"
    echo "$logFile"
    exit 1
fi
logFile="$HOME_DIR/tmp/qupd-golf.log"
cd $PROJECTS_DIR/golf
quasar upgrade 2>&1 | tee $logFile
if cat "$logFile" | grep -qi "Congrats"; then
    echo "ℹ️ Already latest version, skip install/build"
    exit 0
elif cat "$logFile" | grep -qi "install"; then
    # echo "$output"
    echo "✅ Package arts upgraded"
else
    echo "❌ Upgrade error detected:"
    echo "$logFile"
    exit 1
fi
logFile="$HOME_DIR/tmp/qupd-apps.log"
cd $PROJECTS_DIR/apps
quasar upgrade 2>&1 | tee $logFile
if cat "$logFile" | grep -qi "Congrats"; then
    echo "ℹ️ Already latest version, skip install/build"
    exit 0
elif cat "$logFile" | grep -qi "install"; then
    # echo "$output"
    echo "✅ Package arts upgraded"
else
    echo "❌ Upgrade error detected:"
    echo "$logFile"
    exit 1
fi


# if [[ "$1" == "check" ]]; then
#     echo "  ✅ -- Upgrading $PROJECTS_DIR/arts"
#     quasar upgrade -i | tee $logFile 2>&1
# fi

# if [ "$1" == "upd_all" ]; then
#     cd $PROJECTS_DIR/arts
#     echo "  ✅ -- Upgrading $PROJECTS_DIR/art"
#     quasar upgrade -i |tee $logFile 2>&1
# elif [ -f "$logFile" ]; then
#     echo "$logFile exists -- which already upgraded, exit..."
# fi

# logFile="$HOME_DIR/tmp/qupd-yali.log"
# if [ "$1" == "upd_rest" -o "$1" == "upd_all" ]; then
#     cd $PROJECTS_DIR/yali
#     echo "  ✅ -- Upgrading $PROJECTS_DIR/yali"
#     quasar upgrade -i |tee $logFile 2>&1
# elif [ -f "$logFile" ]; then
#     echo "$logFile exists -- which already upgraded, exit..."
# fi

# logFile="$HOME_DIR/tmp/qupd-golf.log"
# if [ "$1" == "upd_rest" -o "$1" == "upd_all" ]; then
#     cd $PROJECTS_DIR/golf
#     echo "  ✅ -- Upgrading $PROJECTS_DIR/golf"
#     quasar upgrade -i |tee $logFile 2>&1
# elif [ -f "$logFile" ]; then
#     echo "$logFile exists -- which already upgraded, exit..."
# fi

# logFile="$HOME_DIR/tmp/qupd-apps.log"
# if [ "$1" == "upd_rest" -o "$1" == "upd_all" ]; then
#     cd $PROJECTS_DIR/apps
#     echo "  ✅ -- Upgrading $PROJECTS_DIR/apps"
#     quasar upgrade -i |tee $logFile 2>&1
# elif [ -f "$logFile" ]; then
#     echo "$logFile exists -- which already upgraded, exit..."
# fi