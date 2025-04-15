#!/bin/bash
today=`date +'%Y-%m-%d'`
echo "checking recording in recorded table for $today ..."

# query="SELECT UTCtoNY(starttime) as starttime, UTCtoNY(endtime) as endtime, LPAD(REPLACE(channum, '_', '-'), 4, ' ') AS chan, 
#   title, basename, autoexpire as aexp FROM recorded r 
#   JOIN channel c on c.chanid = r.chanid WHERE UTCtoNY(starttime) >= '$today'"
  
query="SELECT UTCtoNY(starttime) as starttime, UTCtoNY(endtime) as endtime, LPAD(REPLACE(channum, '_', '-'), 4, ' ') AS chan, 
  title, basename, autoexpire as aexp FROM recorded r 
  JOIN channel c on c.chanid = r.chanid WHERE starttime >= '$today'"

# query="SELECT UTCtoNY(starttime) as starttime, UTCtoNY(endtime) as endtime, LPAD(REPLACE(channum, '_', '-'), 4, ' ') AS chanm, LPAD(title, \
#   (SELECT MAX(LENGTH(title)) from recorded), ' ') AS Title, basename, autoexpire FROM recorded r \
#   JOIN channel c on c.chanid = r.chanid WHERE starttime > '$today'"

# echo $query
mysql -pVVKKll11## -b mythconverg -e"$query order by starttime"

