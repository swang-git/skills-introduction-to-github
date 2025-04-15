#!/bin/bash
if [ $# -ne 1 ]; then
  echo "please provide yearmonth like 201902"
  exit
fi
year=`echo $1|cut -c1-4`
mnth=`echo $1|cut -c5-6`
# sqlx="select * from test_tab"
# sqlx="select sum(a.start_balance) as 'Abalance', b.begin_balance, sum(a.end_balance) as 'Ebalance', b.end_balance \
# from bank_statement_holdings a, bank_statement_assets b where a.year = $year and a.month = $mnth and b.year = $year and b.month = $mnth and b.bank = 'Fidelity'"

sqlx="select sum(a.start_balance) - b.begin_balance, sum(a.end_balance) - b.end_balance \
from bank_statement_holdings a, bank_statement_assets b where a.year = $year and a.month = $mnth and b.year = $year and b.month = $mnth and b.bank = 'Fidelity'"

echo "$1 $year $mnth $sqlx"

echo "$sqlx" | mysql -pVVKKll11@@ dev
