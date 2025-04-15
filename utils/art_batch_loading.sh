#!/bin/bash
COUNTER=72
until [  $COUNTER -lt 2 ]; do
  echo COUNTER -$COUNTER
  # `echo XXX -$COUNTER 2>&1 > lll_x_$COUNTER`
  `/home/swang/prod/LoadArt/PxHY/loading.py -$COUNTER 2>&1 > lll_art_batch_load_PxHY_$COUNTER`
  let COUNTER-=1
done
