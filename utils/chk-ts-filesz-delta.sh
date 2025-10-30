#!/bin/bash
if [ $# -ne 1 ]; then
  echo "please provide basename DO NOT use this one use ~/bin/rects"
  exit
fi
echo "DO NOT use this one use ~/bin/rects"
exit

base=$1
fsza=$(mysql -pYbsjll11 -b mythconverg --batch -N -e "select filesize from recorded where basename = '$basen'")
# echo $fsza
# cnt=2
# ((cnt--))
# echo $cnt
while [ 1 ]; do
    sleep 10
    fszb=$(mysql -pYbsjll11 -b mythconverg --batch -N -e "select filesize from recorded where basename = '$basen'")
    # echo "$fszb - $fsza"
    echo $(($(($fszb - $fsza)) / 1024))
    fsza=$fszb
done

