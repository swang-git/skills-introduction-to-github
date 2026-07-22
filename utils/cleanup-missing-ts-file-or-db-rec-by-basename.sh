#!/bin/bash
if [ $# -ne 1 ]; then
  echo "please provide basename"
  exit
fi

basen=$1
idx=$(expr index "$basen" 'v')
#echo $idx
if [ "$idx" -gt "0" ]; then
  basen=${basen:5}
fi
#echo $basen
if [[ $basen != *.ts ]]; then
   basen="${basen}.ts"
fi
#echo $basen
#exit
##echo
# echo "checking recorded file with basename = $basen"

fileExist="Not Exist $basen in /atv /btv /ctv /dtv /stv"
for dr in /atv /btv /ctv /dtv /stv
do
  FILE="$dr/$basen"
  # echo checking $FILE
  if test -f $FILE; then
    ##_echo $FILE exists
    ##echo "==========================================================================="
    fileExist="Yes, rcfile exists $FILE in $dr"
    # ls -l $FILE
    break
  fi
done
# echo $fileExist
fileEx=${fileExist:0:3}
# echo $fileEx
if [ $fileEx = "Not" ]; then
  cnt=$(mariadb -pYbsjll11 -b mythconverg --batch -N -e "select COUNT(*) from recorded where basename = '$basen'")
  if [ $cnt -gt 0 ]; then
    echo "delete record $basen ...."
    mariadb -pYbsjll11 -b mythconverg -e "delete from recorded where basename = '$basen'"
  else
    echo "No row in recorded table and recorded file for $basen, exiting ..."
  fi
  exit
else
  ##_echo $fileExist
  # echo "And  *.png file is ${FILE}.png"
  # echo "checking record in recorded table"
  record=$(mariadb -pYbsjll11 -b mythconverg --batch -N -e "select COUNT(*) from recorded where basename = '$basen'")
  if [ "$record" -gt 0 ]; then
    ##_echo "Yes, record exists  for $basen in recorded table"
    ##mariadb -pYbsjll11 -b mythconverg -e "select recgroup,recgroupid,watched,recordedid,autoexpire,starttime,bookmarkupdate,LEFT(title,15) as title from recorded where basename='$basen'"
    mariadb -pYbsjll11 -b mythconverg -e "select recgroup,recgroupid as R,autoexpire as AutX,recordedid as Rcdid,recordid as Rcid,ROUND(filesize/1024/1024/1024,1) as '(GB)',SUBSTRING(CONVERT_TZ(starttime,'+00:00','America/New_York'),6,11) as starttime,SUBSTRING(CONVERT_TZ(endtime,'UTC','America/New_York'),6,11) as endtime,LEFT(title,45) as title from recorded where basename='$basen'"
    if [ $fileEx = "Yes" ]; then
      ls -lh $FILE
    fi
    ##echo "==========================================================================="
    exit
  else
    echo "No record in recoded table"
    echo "delete $FILE ...."
    echo "delete ${FILE}.png ...."
    sudo rm $FILE ${FILE}.png
  fi
fi
