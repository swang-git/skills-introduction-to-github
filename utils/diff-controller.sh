#!/bin/bash
if [ $# -ne 1 ]; then
  echo "Usage: $0 backend filename to compare (in general filename is a controller name)"
  exit
fi

fn=$1
devxfile="/sites/projects/golf/src/backend/$fn"
prodfile="/sites/prod/app/Http/Controllers/$fn"
echo "diff $devxfile $prodfile"
diff $devxfile $prodfile
echo
echo "cp $devxfile $prodfile"
echo
