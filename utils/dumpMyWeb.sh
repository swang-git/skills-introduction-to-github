#!/bin/bash
db=MyWeb
dd=`date +%d`
target_file=/BACKUP/db/dump_${dd}_${db}.sql

echo dumping database $target_file

mysqldump -uswang -pvvkk -B $db --routines > $target_file &

