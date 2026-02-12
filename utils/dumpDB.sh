#!/bin/bash
if [ $# -ne 1 ]; then
  # echo "Usage: $0 [database name]"
  echo "Please provide database name"
  exit
fi
db=$1
dd=`date +%a`
d0=$(($dd%2))
target_file=/Users/swang/linmbak/db/dump_${d0}_${db}.sql

echo dumping database $target_file $argv

mysqldump -uswang -pVVKKll11## -B $db --routines > $target_file &
