#!/bin/bash
today=`date +'%Y-%m-%d'%`
echo "updating recorded tv autoexpirations for autoexpire = 1"
# autoexpchk="mysql -pVVKKll11## -b mythconverg -e 'select chanid from recorded where starttime like $today'"
# query="select REGEXP_REPLACE(DATE_SUB(starttime, INTERVAL 5 hour), ':00$', '') as start_time, REGEXP_REPLACE(chanid, '0', '-', 4) as chann, title, autoexpire from recorded where starttime like '$today'"
# query="SELECT REGEXP_REPLACE(DATE_SUB(starttime, INTERVAL 5 hour), ':00$', '') AS start_time, REPLACE(channum, '_', '-') AS chanm, title, autoexpire FROM recorded r \
  # JOIN channel c on c.chanid = r.chanid WHERE starttime like '%$today'"
query="update recorded set autoexpire = 0 where autoexpire = 1"
echo $query
mysql -pVVKKll11## -b mythconverg -e"$query"

