#!/bin/bash

if [ $# -eq 1 ]; then
    echo
    echo "copy monthly fidelity credit card .pdf"
	echo "copy from Documents/FCcard/2023-02-03.pdf to /sites/webdata/docs/fidelity_credit_card"
    echo
	exit
fi

# yyyymmdd=`date +%Y%m%d`
# bddyyyy=`date +%b-%d-%Y`
# # pos=dailydownload/snapshot_${yyyymmdd}
# ppd=Portfolio_Positions_${bddyyyy}
# Ym=`date +%Y_%m`
# Wn=`date +%a`
yyyy=`date +%Y`
mm=`date +%m`
# m1=`expr $mm + 1`
m1=$(($mm + 1))
mx=$m1
if [ $m1 -lt 10 ]; then
    mx="0$m1"
fi
yyyymm03pdf=${yyyy}-${mx}-03.pdf
# echo $yyyymm03pdf

echo "cp /home/swang/Documents/FCcard/$yyyymm03pdf /sites/webdata/docs/fidelity_credit_card/"
## cp /home/swang/Documents/FCcard/$yyyymm03pdf /sites/webdata/docs/fidelity_credit_card/

