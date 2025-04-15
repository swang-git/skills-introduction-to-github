#!/bin/bash

if [ $# -ne 2 ]; then
	echo "Please provide tag name and day<PXHY|PXWW|PXQG|PXZJ|PXWX> <20190219>"
    echo "backup image files for anyday in a-monthly fashion. i.e. a file for a month"
	exit
fi

pxtag=$1
tday=$2
tyear=`echo $tday|cut -c1-4`
tmon=`echo $tday|cut -c5-6`
# echo $pxtag $tday $tyear $tmon

# #Ym=`date +%Y_%m`
# yesterday=`date -d '1 day ago' '+%Y%m%d'`
# Mo=`echo $yesterday|cut -c5-6`
# Yr=`echo $yesterday|cut -c1-4`

tag_dir=/etv/BAK/px/${pxtag}
tar_file=${tag_dir}/${tyear}_${tmon}.tar

dat_dir=/sites/webdata/daily_data
src_dir=${pxtag}/${tyear}/${tday}/images

if [ ! -d "${dat_dir}/$src_dir" ]; then
	echo no such directory ${dat_dir}/$src_dir -- NO images for $yesterday do nothing -- exiting
	exit
fi

if [ ! -d $tag_dir ]; then
    mkdir -p $tag_dir
fi


# # echo tar fuv -v $tar_file $src_dir
echo tar fuv $tar_file $src_dir

cd $dat_dir
tar fuv $tar_file $src_dir    ## updating with yesterday images
cd /home/swang
