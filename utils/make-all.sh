#!/bin/bash

if [ $# -ne 1 ]; then
    echo "Please provide env:prod or devx"
    exit
fi
envx=$1

#cd /Users/swang/sites/projects/arts
make-quasar $envx arts |tee  ~/tmp/makeall-$envx-arts.log 2>&1

#cd /Users/swang/sites/projects/yali
make-quasar $envx yali |tee ~/tmp/makeall-$envx-yali.log 2>&1

#cd /Users/swang/sites/projects/golf
make-quasar $envx golf |tee ~/tmp/makeall-$envx-golf.log 2>&1

#cd /Users/swang/sites/projects/apps
make-quasar $envx apps |tee ~/tmp/makeall-$envx-apps.log 2>&1
