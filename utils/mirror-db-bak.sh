#!/bin/bash

if [ $# -eq 1 ]; then
    echo
	echo " Mirror Backup files from /bak/db to /dtv/BAK/db"
    echo
	exit
fi

Yr=`date +%Y`
Ym=`date +%Y_%m`
Wn=`date +%a`

db_dir=/f36/bak/db
gz_file=$db_dir/dumpz_${Wn}_*.sql.gz
ta_dir=/dtv/BAK/db
echo "cp -p $gz_file $ta_dir"
cp -p $gz_file $ta_dir

# dc_dir=/bak/dc
# tar_file=${dc_dir}/${Yr}.tar
# ta_dir=/dtv/BAK/dc
# echo "gzip -c $tar_file > $ta_dir/$Yr.tar.gz"
# gzip -c $tar_file > $ta_dir/$Yr.tar.gz

# px_dir=/bak/px
# tar_file=${px_dir}/PXWX/${Ym}.tar
# ta_dir=/dtv/BAK/px/PXWX
# echo "gzip -c $tar_file > $ta_dir/$Ym.tar.gz"
# gzip -c $tar_file > $ta_dir/$Ym.tar.gz

# px_dir=/bak/px
# tar_file=${px_dir}/PXWW/${Ym}.tar
# ta_dir=/dtv/BAK/px/PXWW
# echo "gzip -c $tar_file > $ta_dir/$Ym.tar.gz"
# gzip -c $tar_file > $ta_dir/$Ym.tar.gz



# # px_dir=/bak/px
# # tar_file=${px_dir}/PXQG/${Ym}.tar
# # ta_dir=/dtv/BAK/px/PXQG
# # gzip -c $tar_file > $ta_dir/$Ym.tar.gz

# # px_dir=/bak/px
# # tar_file=${px_dir}/PXJL/${Ym}.tar
# # ta_dir=/dtv/BAK/px/PXJL
# # gzip -c $tar_file > $ta_dir/$Ym.tar.gz

# # px_dir=/bak/px
# # tar_file=${px_dir}/PXHY/${Ym}.tar
# # ta_dir=/dtv/BAK/px/PXHY
# # gzip -c $tar_file > $ta_dir/$Ym.tar.gz
