#!/bin/bash
#
# rename all *.jpg files to yaYYYYMMDD_0x.jpg
#

tag=ya`date +%Y%m%d`
## echo tag=$tag; exit

n=1;
for f in *.jpg; do
   mv "$f" "$(printf "${tag}_%02d.jpg" $n)"; 
   ((n++));
done
