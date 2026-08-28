#!/bin/bash
export PATH="/bin:/opt/homebrew/bin:/usr/local/mysql/bin:/Users/swang/bin:/opt/homebrew/opt/node@22/bin"
if [ $# -ne 1 ]; then
  echo "Please provide database name"
  exit
fi
db=$1
dd=`date +%w`
if [ $dd -eq 0 ]; then 
  dd=7
  ##echo "new value for dd=$dd"
fi
d0=$(($dd%2))
target_file=/Users/swang/BAK/db/dump_${d0}_${db}.sql

echo dumping database $target_file $argv

mariadb-dump -B $db --routines > $target_file &

#if [ $db == "prod" ]; then
  # do this on fedora side
  #/Users/swang/bin/sync2fedora $db
  ##mariadb-dump -uswang -pYbsjll11 -B $db --routines | mariadb -uswang -pYbsjll11 -hfedora
  ##mysqldump -uswang -pYbsjll11 -B $db --routines | mysql -uswang -pYbsjll11 -hmaci
#fi
