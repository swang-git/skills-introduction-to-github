#!/bin/bash
if [ $# -ne 1 ]; then
  # echo "Usage: $0 [database name]"
  echo "Please provide database name"
  exit
fi
db=$1
dd=`date +%a`
target_file=/Users/swang/linmbak/db/dumpz_${dd}_${db}.sql.gz

echo dumping database $target_file $argv

/usr/local/mysql/bin/mysqldump -B $db --routines -pVVKKll11## | gzip > $target_file &
#mysqldump -uswang -pVVKKll11## -B $db --routines | gzip > $target_file &
# if [ $db = "golf" ]; then
#   mysqldump -uswang -pVVKKll11## -B $db --skip-comments --skip-opt --complete-insert --add-drop-table | gzip > $target_file & // dump file too big
#   ##echo "AA $db"
# else
#   mysqldump -uswang -pVVKKll11## -B $db --routines | gzip > $target_file &
#   ##echo "BB $db"
# fi

## use following for loading views from golf to golf_dev
# mysqldump golf alias_handicaps -uswang -pVVKKll11##|mysql -B golf_dev -pVVKKll11##
# mysqldump golf alias_handicap_KJ_view -uswang -pVVKKll11##|mysql -B golf_dev -pVVKKll11##
# mysqldump golf alias_handicap_view -uswang -pVVKKll11##|mysql -B golf_dev -pVVKKll11##
# mysqldump golf match_player_handicaps_view -uswang -pVVKKll11##|mysql -B golf_dev -pVVKKll11##
