#!/bin/bash
# echo $#
if [ $# -ne 5 ]; then
    echo "please provide tag[PXJL] year[2020] month[06] start_date[05] end_date[20200631], all dates must be within the provided month(here like within 06)"
    exit
fi
tag=$1
year=$2
month=$3
start_date=$4
end_date=$5
echo "tar imgs from $year$month$start_date to $end_date"
thedate=$year$month$start_date

cd /sites/webdata/daily_data
echo
echo "working on the date: $thedate in directory `pwd`"
echo

cmd="tar fuv /etv/BAK/px/$tag/${year}_${month}.tar $tag/$year/$thedate/images"
echo "exec cmd: $cmd"
$cmd
until [ $thedate -eq $end_date ]; do
    let "thedate=thedate+1"
    cmd="tar fuv /etv/BAK/px/$tag/${year}_${month}.tar $tag/$year/$thedate/images"
    echo "exec cmd: $cmd"
    $cmd
done
# tar fuv /etv/BAK/px/PXJL/2020_06.tar PXJL/2020/20200605/images