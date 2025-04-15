#!/bin/bash
#  mysql commands to show deletepending recordings
PASS=`grep Password /home/swang/.mythtv/config.xml | sed 's/<\/*Password>//g' | sed 's/ *//g'`
SQLLINE='select deletepending, recgroup, count(*) from recorded group by deletepending, recgroup;' 
echo $SQLLINE1 | mysql --database=mythconverg --user=mythtv --password=${PASS} 2>&1 | grep -v Warning
