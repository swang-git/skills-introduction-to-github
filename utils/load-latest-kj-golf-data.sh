#!/bin/bash
if [ $# -eq 1 -a "$1" == "-o" ]; then
  echo "To load old data:"
  echo "  cd /sites/prod"
  echo "  php artisan loadx:KjOldData /sites/tmp/kjsfiles/kjsfile_Tue_18.xlsx L (where L is the colname for the date)" 
  exit
elif [ $# -eq 1 -a "$1" == "-h" ]; then
  echo "Usage: $0 <Golf.xlsx URL>"
  exit
fi
# exit
# url=$1
# url=/home/swang/kjsfile-2023-04-23.xlsx
url=http://mmsgolf.com/kj/Golf.xlsx
## cd /sites/devx
cd /sites/prod
php artisan loadx:kjgolf $url
# php artisan loadx:kjgolf /home/swang/kjsfile-2023-04-23.xlsx
