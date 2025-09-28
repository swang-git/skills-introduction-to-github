#!/bin/bash
if [ $# -ne 1 ]; then
  echo "please provide basename"
  exit
fi

basen=$1
fsza=$(mysql -pYbsjll11 -b mythconverg --batch -N -e "select filesize from recorded where basename = '$basen'")
while [ 1 ]; do
    sleep 10
    fszb=$(mysql -pYbsjll11 -b mythconverg --batch -N -e "select filesize from recorded where basename = '$basen'")

    # echo "$fszb - $fsza"
    echo $(($(($fszb - $fsza)) / 1024))
    fsza=$fszb
done

