#!/bin/bash
if [ $# -ne 1 ]; then
  echo "please provide basename"
  exit
fi

basen=$1
# echo "checking recorded file with basename = $basen"

# dir "/home/swang/atv/$basen"
# dir "/home/swang/btv/$basen"
# dir "/home/swang/ctv/$basen"
# dir "/home/swang/dtv/$basen"
# dir "/home/swang/etv/$basen"

for dr in atv btv ctv dtv etv
do
  FILE="/home/swang/$dr/$basen"
  # echo checking $FILE
  if test -f $FILE; then
    echo
    echo $FILE exists
    echo
    exit
  fi
done

echo
echo $basen NOT exist in recgroup. delete it from table recorded
echo
mysql -pVVKKll11## -b mythconverg -e "delete from recorded where basename = \"$basen\""

# autoexpchk="mysql -pVVKKll11## -b mythconverg -e 'select chanid from recorded where starttime like $today'"
# query="select REGEXP_REPLACE(DATE_SUB(starttime, INTERVAL 5 hour), ':00$', '') as start_time, REGEXP_REPLACE(chanid, '0', '-', 4) as chann, title, autoexpire from recorded where starttime like '$today'"
# query="SELECT REGEXP_REPLACE(DATE_SUB(starttime, INTERVAL 5 hour), ':00$', '') AS start_time, REPLACE(channum, '_', '-') AS chanm, title, autoexpire FROM recorded r \
  # JOIN channel c on c.chanid = r.chanid WHERE starttime like '%$today'"
# query="update recorded set autoexpire = 0 where autoexpire = 1"
# echo $query
# mysql -pVVKKll11## -b mythconverg -e"$query"

