#!/usr/bin/bash
if [ $# -ne 4 ]; then
  echo "Please provide tag[PXQG, PXJL] and dyx[ -1, -2, etc] and duration[2.5m, etc] and start_page[1, 2, ... 14]"
  exit
fi
tag=$1
dyx=$2
dur=$3
start_page=$4
for i in {1..20}
do
  echo
  # echo ">>> doing loop $i for load$tag $dyx for duration $dur <<<<"
  echo ">>> doing loop $i for load-old-$tag $dyx $start_page for duration $dur <<<<"
  echo
  timeout -s 9 $dur load$tag $dyx $start_page
  # timeout -s 9 $dur load-old-$tag $dyx
  # echo sleep for 3 seconds
  # sleep 3
  # echo start again
  if [ $? = 0 ]
    then
      break
  fi
done
