#!/usr/bin/bash
tag=HY
tag=JL
tag=QG
#day=33
#echo $tag
#loadPXHY -33 2>&1 tee /tmp/loadPXHY_33.log
for day in {25..1..-1}
  do
    loadPX${tag} -$day 2>&1|tee /tmp/loadPX${tag}_${day}.log
  done