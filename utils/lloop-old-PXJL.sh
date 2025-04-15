#!/usr/bin/bash
for k in {1..70}
do
  idx=`expr 70 - $k`
  echo doing loop $idx
  /home/swang/bin/loop-load-px PXJL -$idx 1.4m 2>&1|tee log/logs/px/lopp-load-px-JL_$idx.log
done 