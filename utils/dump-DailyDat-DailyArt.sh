#!/bin/bash
echo '=========== starting ============'
if [ $# -ne 1 ]; then
  # echo "Usage: $0 [table name: DailyDat/Art]"
  echo "Please provide table name: DailyDat or DailyArt"
  exit
fi

yymd=`date -v-1d +%Y-%m-%d`
wkd=`date +%a`

tab=$1
target_file=/Users/swang/linmbak/db/dump_${wkd}_${tab}.sql
# echo target_file: [$target_file1] yesterday: [$yymd]
cmd="mysqldump -pVVKKll11## MyWeb $tab --where tim>='$yymd'"
echo
echo command: [$cmd]
$cmd > $target_file

# tab1=$1
# target_file1=/Users/swang/linmbak/db/dump_${wkd}_${tab1}.sql
# echo target_file: [$target_file1] yesterday: [$yymd]
# cmd1="mysqldump -pVVKKll11## MyWeb $tab1 --where tim>='$yymd' > $target_file1"
# echo
# echo command: [$cmd1]

# tab2='DailyDat'
# target_file2=/Users/swang/linmbak/db/dump_${wkd}_${tab2}.sql
# echo
# echo target_file: [$target_file2] $yymd
# cmd2="mysqldump -pVVKKll11## MyWeb $tab2 --where tim>='$yymd' > $target_file2"
# echo
# echo command: [$cmd2]

# dumpDB prod
# dumpDB golf
# $cmd1
# $cmd2


