#!/usr/bin/bash
### df -h /home/swang/etv
### df -h /home/swang/htv | grep -v Filesystem
### df -h /home/swang/mtv | grep -v Filesystem
## df -Th /home/swang/?tv; df -Th /home|grep -v Filesystem
## df -h /home/swang/?tv; df -h /home|grep -v Filesystem
## df -h /home/swang/?tv|grep -v Filesystem
## df -h /?tv|grep -v Filesystem -v ntv
####df -h /xtv /ctv /dtv
header=`df -h /dtv | grep Filesystem`
echo "${header}:"
##df -h /atv | grep -v Filesystem 
btv=`df -h /btv | grep -v Filesystem`
echo "${btv}btv"
df -h /ctv /dtv /stv | grep -v Filesystem
