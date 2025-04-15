#!/bin/bash
if [ $# -ne 3 ]; then
    echo "please provide tablenme dbname dbdumpfile.gz"
    exit
fi
tb=$1
db=$2
fz=$3
ur=swang
pw=VVKKll11@@
echo loading table \"$tb\" to database \"$db\" from gz dumpfile \"$fz\"

echo 1st. truncate the table $db.$tb
mysql -e "truncate $tb" $db -u$ur -p$pw
echo 2nd. loading $db.$tb from file $fz
zgrep ^"INSERT INTO \`$tb\`" $fz | mysql $db -u$ur -p$pw
