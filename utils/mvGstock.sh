#!/bin/bash
if [ $# -eq 1 ] && ([ $1 == "-help" ] || [ $1 == "-h" ]);  then
    echo
    echo "copy daily stock files .pdf"
    echo "copy from Documents/stocks/20250629_CHTR.pdf to /sites/webdata/docs/gstocks"
    echo
    exit
fi

ymd=`date +%Y%m%d`

if [ $# -eq 1 ]; then
  ymd=$1
fi

echo $ymd
echo "cp -p /Users/swang/Documents/gstocks/${ymd}_*.pdf /Users/swang/sites/webdata/docs/gstocks"
cp -p /Users/swang/Documents/gstocks/${ymd}_*.pdf /Users/swang/sites/webdata/docs/gstocks

