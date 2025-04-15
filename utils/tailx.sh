#!/usr/bin/bash
if [[ "$1" != 'prod' && "$1" != 'devx' && "$1" != 'myth' && "$1" != 'divx' ]]; then
    echo "Usage: tailx [devx|prod|myth]"
    exit
fi
# echo $1
if [ "$1" = "myth" ]; then
    tail -1000f /var/log/mythtv/mythbackend.*.*.log
else
    tail -1000f /sites/$1/storage/logs/laravel.log
fi
