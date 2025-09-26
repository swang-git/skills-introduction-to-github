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
    echo "==========================================================================="
    fileExist="Yes, rcfile exists $FILE in $dr"
    # ls -l $FILE
    break
  fi
done
# echo $fileExist
fileEx=${fileExist:0:3}
# echo $fileEx
if [ $fileEx = "Not" ]; then
  cnt=$(mysql -pYbsjll11 -b mythconverg --batch -N -e "select COUNT(*) from recorded where basename = '$basen'")
  if [ $cnt -gt 0 ]; then
    echo "delete record $basen ...."
    mysql -pYbsjll11 -b mythconverg -e "delete from recorded where basename = '$basen'"
  else
    echo "No row in recorded table and recorded file for $basen, exiting ..."
  fi
  exit
else
  echo $fileExist
  # echo "checking record in recorded table"
  record=$(mysql -pYbsjll11 -b mythconverg --batch -N -e "select COUNT(*) from recorded where basename = '$basen'")
  if [ "$record" -gt 0 ]; then
    echo "Yes, record exists  for $basen in recorded table"
    mysql -pYbsjll11 -b mythconverg -e "select watched,chanid,starttime,endtime,title from recorded where basename='$basen'"
    if [ $fileEx = "Yes" ]; then
      ls -lh $FILE
    fi
    echo "==========================================================================="
    exit
  else
    echo "No record in recoded table"
    echo "delete $FILE ...."
    sudo rm $FILE
  fi
fi
