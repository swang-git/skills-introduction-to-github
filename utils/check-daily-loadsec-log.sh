#!/usr/bin/bash
if [ $# -eq 0 ]; then
    echo "Please provide 0, 1, 2, ...6 for checking Sun, Mon, Tue, ... Sat loadsec_Wkday.log"
    exit
fi
wkd=`date -d "$(( 7-$(date '+%u')+$1 )) days" '+%a'`
fnm="/home/swang/tmp/logs/sc/loadsec_$wkd.log"
cmd="cat $fnm"
echo $cmd
$cmd

# wk=`date +%a`
# ho=`date +%H`
# mo=`date +%M`

# #echo "$wk $ho $mo"

# hx=`expr $ho % 10`
# mx=`expr $mo / 10`

# echo "$wk $hx $mx"
# if [ $hx -eq 9 ]; then
#     cmd="   cat /home/swang/tmp/logs/sc/loadsec_${wk}09[0-$mx]0.log"
# else
#     cmd="   cat /home/swang/tmp/logs/sc/loadsec_$wk[0-1][0-$hx][0-$mx]0.log"
# fi
# $cmd
# echo "$cmd"
