#!/bin/bash
#
# timestamps *.jpg files to based on $1
#

if [ $# -eq 0 ]; then
   echo "need a date:CCYYMMDD"
fi
DATE_STR=$1
n=101;
tmstamp="${DATE_STR}0$n"
# for f in *.jpg(Nom); do
# CORRECT loop: safe for spaces, in mtime asc order (same as ls -t)
ls -t *.jpg | while IFS= read f; do
   # echo "tmstamp=$tmstamp"
   touch -t "$tmstamp" "$f" 
   tmstamp=$(($tmstamp + 1))
done
