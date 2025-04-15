#!/bin/bash

if [ $# -ne 1 ]; then
	echo "Please provide tag name <PXHY|PXWW|PXQG|PXZJ|PXWX>"
    echo "backup image files daily in a-monthly fashion. i.e. a file for a month"
	exit
fi

PXtag=$1
#Ym=`date +%Y_%m`
yesterday=`date -d '1 day ago' '+%Y%m%d'`
Mo=`echo $yesterday|cut -c5-6`
Yr=`echo $yesterday|cut -c1-4`

tag_dir=/f36/bak/px/${PXtag}
tar_file=${tag_dir}/${Yr}_${Mo}.tar

dat_dir=/sites/webdata/daily_data
src_dir=${PXtag}/${Yr}/${yesterday}/images

if [ ! -d "${dat_dir}/$src_dir" ]; then
	echo no such directory ${dat_dir}/$src_dir -- no images for $yesterday do nothing -- exiting
	exit
fi

if [ ! -d $tag_dir ]; then
    mkdir -p $tag_dir
fi


# echo tar fuv -v $tar_file $src_dir
echo tar fuv $tar_file $src_dir

cd $dat_dir
tar fuv $tar_file $src_dir    ## updating with yesterday images
cd /home/swang
