#!/usr/bin/bash

wk=`date +%a`
ho=`date +%H`
mo=`date +%M`

#echo "$wk $ho $mo"

hx=`expr $ho % 10`
mx=`expr $mo / 10`

echo "$wk $hx $mx"
if [ $hx -eq 9 ]; then
    cmd="   cat /home/swang/tmp/logs/sc/loadsec_${wk}09[0-$mx]0.log"
else
    cmd="   cat /home/swang/tmp/logs/sc/loadsec_$wk[0-1][0-$hx][0-$mx]0.log"
fi
$cmd
echo "$cmd"
