#!/bin/bash
if [ $# -eq 1 ] && ([ $1 == "-help" ] || [ $1 == "-h" ]);  then
    echo
    echo "copy daily position files .pdf and .csv"
    echo "copy from Documents/dailydownload/snapshots_20230112.pdf to /sites/webdata/docs/Portfolio"
    echo "copy from Documents/dailydownloads/Portfolio_Positions_Jan-02-2023.csv to /sites/webdata/docs/Portfolio"
    echo
    exit
fi

yyyymmdd=`date +%Y%m%d`
bddyyyy=`date +%b-%d-%Y`
# pos=dailydownload/snapshot_${yyyymmdd}
# ppd=Portfolio_Positions_${bddyyyy}
Ym=`date +%Y_%m`
Wn=`date +%a`

if [ $# -eq 1 ]; then
  yyyymmdd=$1
  bddyyyy=`date -d$1 +%b-%d-%Y`
fi

echo $yyyymmdd
echo $bddyyyy
ppd=Portfolio_Positions_${bddyyyy}

## echo "cp /home/swang/Documents/dailydownload/snapshot_$yyyymmdd.pdf /sites/webdata/docs/Portfolio/"
## echo "cp /home/swang/Documents/dailydownload/$ppd.csv /sites/webdata/docs/Portfolio/snapshot_$yyyymmdd.csv"
cp /Users/swang/Documents/dailydownload/snapshot_$yyyymmdd.pdf /Users/swang/sites/webdata/docs/Portfolio/
cp /Users/swang/Downloads/$ppd.csv /Users/swang/sites/webdata/docs/Portfolio/snapshot_$yyyymmdd.csv

