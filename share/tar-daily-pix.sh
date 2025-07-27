#!/bin/bash

if [ $# -ne 1 ]; then
	echo "Please provide tag name <PXHY|PXWW|PXQG|PXZJ|PXWX>"
	exit
fi

px=$1
yr=`date +%Y`
##yesterday=`date -d '0 day ago' '+%Y%m%d'`
yesterday=`date -d '1 day ago' '+%Y%m%d'`

tar_file=/media/BACK_UP/${yr}_${px}.tar
src_dirf=daily_data/$px/$yr/$yesterday/images

## tar --delete --file=/media/BACK_UP/2017_PXHY.tar daily_data/PXHY/2017/20171005
echo
echo update $tar_file with $src_dirf
echo

if [ ! -d "/home/swang/sites/webdata/$src_dir" ]; then
	echo no such directory /home/swang/sites/webdata/$src_dir, exiting
	exit
fi

echo tar fuv $tar_file $src_dirf
echo

cd /home/swang/sites/webdata
tar  fuv $tar_file $src_dirf    ## updating with yesterday images
cd ~/
