#!/bin/bash

if [ $# -eq 1 ]; then
    echo
	echo " Mirror Backup files from /bak/[dc|px] to /dtv/BAK/[dc|pc]"
    echo
	exit
fi

Yr=`date +%Y`
Ym=`date +%Y_%m`
Wn=`date +%a`

dd=`date +%d`
if [ "X$dd" = "X01" ]; then
    Ym=`date -d '1 month ago' +%Y_%m`
fi
#echo $Ym "X$dd"

# db_dir=/bak/db
# gz_file=$db_dir/dumpz_${Wn}_*.sql.gz
# ta_dir=/dtv/BAK/db
# cp $gz_file $ta_dir


dc_dir=/f36/bak/dc
tar_file=${dc_dir}/${Yr}.tar
ta_dir=/dtv/BAK/dc
echo "gzip -c $tar_file > $ta_dir/$Yr.tar.gz"
gzip -c $tar_file > $ta_dir/$Yr.tar.gz
