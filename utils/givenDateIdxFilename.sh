#!/bin/bash
#
# rename all *.jpg files to a given date (indexed) to yaYYYYMMDD_0idx.jpg
#
echo "\n date indexing jpg file to a given date_0idx"
if [ $# -eq 0 ]; then
   echo "a date (CCYYMMDD) required"
   exit
fi

tag="ya$1"
echo tag=$tag;

n=1;
# for f in $(ls -tr *.jpg); do
# CORRECT loop: safe for spaces, sorted OLDEST first (ls -tr)
ls -tr *.jpg | while IFS= read -r f; do
   # echo "$(printf "${tag}_%02d.jpg" $n)"; 
   mv "$f" "$(printf "${tag}_%02d.jpg" $n)"; 
   ((n++));
done
