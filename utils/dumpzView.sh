#!/bin/bash
if [ $# -ne 1 ]; then
  echo "Please provide database name"
  exit
fi
db=$1
dd=`date +%a`
target_file=/f36/bak/db/dumpz_${dd}_${db}_views.sql.gz
dbpwd=VVKKll11##

echo dumping views in database $target_file $argv

## mysqldump -B $db --routines -pVVKKll11## | gzip > $target_file &

# mysql -pVVKKll11## golf --skip-column-names -B -e "show full tables where table_type='VIEW'"|awk '{print $1}' | xargs -I {} mysqldump -pVVKKll11## golf {} | gzip > $target_file &
mysql -p$dbpwd $1 --skip-column-names -B -e "show full tables where table_type='VIEW'"|awk '{print $1}' | xargs -I {} mysqldump -p$dbpwd $1 {} | gzip > $target_file &