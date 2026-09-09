#!/bin/bash
cd /Users/swang/sites/projects/arts
quasar upgrade -i |tee  ~/tmp/qupd-arts.log 2>&1

cd /Users/swang/sites/projects/yali
quasar upgrade -i |tee  ~/tmp/qupd-yali.log 2>&1

cd /Users/swang/sites/projects/golf
quasar upgrade -i |tee  ~/tmp/qupd-golf.log 2>&1

cd /Users/swang/sites/projects/apps
quasar upgrade -i |tee  ~/tmp/qupd-apps.log 2>&1
