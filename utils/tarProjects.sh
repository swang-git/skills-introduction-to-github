#!/bin/bash
dd=`date +%a`
target_file=/dtv/BAK/pj/projects_${dd}.tgz

##cd /sites
cd /home/swang
echo tar cfz $target_file projects 

tar cfz $target_file projects &

cd ~

