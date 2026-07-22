#!/Users/swang/myenv/bin/python
import sys, os, time, re
from Models import StockQuote, Portfolio
import argparse
from sqlalchemy import create_engine, func
from sqlalchemy.orm import sessionmaker
import sys, time
from datetime import datetime, timedelta
from datetime import date
from datetime import datetime
from Utils import printHeader, printTailer, displaySec, padsp
from Models import dbsession

parser = argparse.ArgumentParser()
parser.add_argument("show_all_in_the_day", metavar='int', type=int, nargs='?', default=0, \
                        help='all quotes in the table for the day, default 0, i.e. only show the latest in quotes')
parser.add_argument("add_days", metavar='int', type=int, nargs='?', default='0', help='add/sub days from today, default 0 for today')
# optional arguments
parser.add_argument('-d', '--db', type=str, default='prod', help='check quotes in this database default database: prod')
args = parser.parse_args()
add_days = args.add_days
thedayin = args.show_all_in_the_day
database = args.db

def get_quote_rows(thedayin):
    load_date = (datetime.now() + timedelta(days=add_days)).strftime('%Y-%m-%d')
    rows = dbsession(database).query(StockQuote).filter(func.date_format(StockQuote.load_time, '%Y-%m-%d').label('formated_date')==load_date)
    if thedayin:
        rows = rows.order_by(StockQuote.load_time.asc()).all()
    else:
        rows = rows.order_by(StockQuote.load_time.desc()).all()
    return [load_date, rows]

def main():
    load_date, rows = get_quote_rows(thedayin)
    if len(rows) <= 0: ## no data in DB
        print(f"STOCK QUOTES NOT LOADED YET in database={database} FOR {load_date}, EXITING ...")
        sys.exit(0)
    else:
        print(f'                         STOCK QUOTES from {database}.stock_quotes LOADED at {rows[0].load_time}')

        symbols = ['T', 'WBD', 'CHTR', 'DELL', 'CSCO', 'MSFT', 'BEKE']
        shares = {'T': 287, 'WBD': 69, 'CHTR':20, 'DELL':36, 'CSCO':640, 'MSFT':400, 'BEKE':100}
        nsp = 0
        sp = padsp('', nsp)
        # startm = time.time()
        totalValue = 0
        printHeader(sp)
        for s in symbols:
            r = next((row for row in rows if row.symbol == s), None) ## return the first match
            quantity = shares[s]
            portfolio = Portfolio(r, quantity)
            displaySec(portfolio, sp)
            totalValue += float(portfolio.values)
        totalValue = f"{totalValue:,.2f}"
        printTailer(sp, totalValue)
if __name__=="__main__": main()
