#!/bin/bash

# if [ $# -ne 1 ]; then
# 	echo "Please provide tag name <PXHY|PXWW|PXQG|PXZJ|PXWX>"
#     echo "backup image files daily in a-monthly fashion. i.e. a file for a month"
# 	exit
# fi

# PXtag=$1
#Ym=`date +%Y_%m`
yesterday=`date -d '1 day ago' '+%Y%m%d'`
Yr=`echo $yesterday|cut -c1-4`

doc_dir=/f36/bak/dc
tar_file=${doc_dir}/${Yr}.tar

docs_dir=/sites/webdata/docs

if [ ! -d $doc_dir ]; then
    mkdir -p $doc_dir
fi


# echo tar fuv -v $tar_file $src_dir
echo tar fuv $tar_file $docs_dir

cd /sites/webdata
tar fuv $tar_file docs    ## updating with yesterday images
cd /home/swang
