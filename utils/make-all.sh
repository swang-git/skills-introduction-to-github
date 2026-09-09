#!/bin/bash
cd /Users/swang/sites/projects/arts
make-quasar prod arts |tee  ~/tmp/makeall-arts.log 2>&1

cd /Users/swang/sites/projects/yali
make-quasar prod yali |tee ~/tmp/makeall-yali.log 2>&1

cd /Users/swang/sites/projects/golf
make-quasar prod golf |tee ~/tmp/makeall-golf.log 2>&1

cd /Users/swang/sites/projects/apps
make-quasar prod apps |tee ~/tmp/makeall-apps.log 2>&1
