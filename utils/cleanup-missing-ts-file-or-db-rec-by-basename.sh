#!/bin/bash
if [ $# -ne 1 ]; then
  echo "please provide basename"
  exit
fi

basen=$1
idx=$(expr index "$basen" 'v')
# echo $idx
if [ "$idx" -gt "0" ]; then
  basen=${basen:5}
fi
# echo $basen
# exit
echo
# echo "checking recorded file with basename = $basen"

fileExist="Not Exist $basen in /atv /btv /ctv /dtv /stv"
for dr in /atv /btv /ctv /dtv /stv
do
  FILE="$dr/$basen"
  # echo checking $FILE
  if test -f $FILE; then
    #echo $FILE exists
    fileExist="Yes, rcfile exists $FILE"
    # ls -l $FILE
    break
  fi
done
# echo $fileExist
fileEx=${fileExist:0:3}
# echo $fileEx
if [ $fileEx = "Not" ]; then
  echo "delete record $basen ...."
  mysql -pYbsjll11 -b mythconverg -e "delete from recorded where basename = '$basen'"
  exit
else
  echo $fileExist
  # echo "checking record in recorded table"
  record=$(mysql -pYbsjll11 -b mythconverg --batch -N -e "select COUNT(*) from recorded where basename = '$basen'")
  if [ "$record" -gt 0 ]; then
    echo "Yes, record exists for $basen in the recorded table"
    mysql -pYbsjll11 -b mythconverg -e "select watched,chanid,title from recorded where basename='$basen'"
    if [ $fileEx = "Yes" ]; then
      ls -lh $FILE
    fi
    exit
  else
    echo "No record in recoded table"
    echo "delete $FILE ...."
    sudo rm $FILE
  fi
fi

exit

echo
echo $basen NOT exist in recgroup. delete it from table recorded
echo
### mysql -pVVKKll11## -b mythconverg -e "delete from recorded where basename = \"$basen\""
mysql -pYbsjll11 -b mythconverg -e "delete from recorded where basename = \"$basen\""

# autoexpchk="mysql -pVVKKll11## -b mythconverg -e 'select chanid from recorded where starttime like $today'"
# query="select REGEXP_REPLACE(DATE_SUB(starttime, INTERVAL 5 hour), ':00$', '') as start_time, REGEXP_REPLACE(chanid, '0', '-', 4) as chann, title, autoexpire from recorded where starttime like '$today'"
# query="SELECT REGEXP_REPLACE(DATE_SUB(starttime, INTERVAL 5 hour), ':00$', '') AS start_time, REPLACE(channum, '_', '-') AS chanm, title, autoexpire FROM recorded r \
  # JOIN channel c on c.chanid = r.chanid WHERE starttime like '%$today'"
# query="update recorded set autoexpire = 0 where autoexpire = 1"
# echo $query
# mysql -pVVKKll11## -b mythconverg -e"$query"

