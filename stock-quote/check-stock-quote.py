#!/Users/swang/myenv/bin/python
import sys
from datetime import datetime, timedelta
import argparse
from sqlalchemy import func
from more_itertools import chunked

from Utils import printHeader, printTailer, displaySec, padsp
from Models import dbsession, StockQuote, Portfolio

parser = argparse.ArgumentParser()
parser.add_argument("add_days", metavar='int', type=int, nargs='?', default='0', help='add/sub days from today, default 0 for today')
# optional arguments
parser.add_argument('-d', '--db', type=str, default='prod', help='check quotes in this database default database: prod')
args = parser.parse_args()
add_days = args.add_days
database = args.db

def get_quote_rows():
    load_date = (datetime.now() + timedelta(days=add_days)).strftime('%Y-%m-%d')
    rows = dbsession(database).query(StockQuote)\
        .filter(func.date_format(StockQuote.load_time, '%Y-%m-%d').label('formated_date')==load_date)\
        .order_by(StockQuote.load_time.asc()).all()
    return [load_date, rows]

def main():
    load_date, rows = get_quote_rows()
    if len(rows) <= 0: ## no data in DB
        print(f"STOCK QUOTES NOT LOADED YET in database={database} FOR {load_date}, EXITING ...")
        sys.exit(0)
    else:
        print(padsp(' ', 31) + f'STOCK QUOTES from {database}.stock_quotes LOADED at {rows[0].load_time}')
        symbols = ['T', 'WBD', 'CHTR', 'DELL', 'CSCO', 'MSFT', 'BEKE']
        shares = {'T': 287, 'WBD': 69, 'CHTR':20, 'DELL':36, 'CSCO':640, 'MSFT':400, 'BEKE':100}
        sp = ''
        num_of_stocks = 7
        for chunk in chunked(rows, num_of_stocks):
            # if len(chunk) <= 0:break
            totalValue = 0
            printHeader(sp)
            for row in chunk:
                quantity = shares[row.symbol]
                portfolio = Portfolio(row, quantity)
                displaySec(portfolio, sp)
                if row.symbol == 'BEKE':
                    continue
                else:
                    totalValue += float(portfolio.values)
            totalValue = f"{totalValue:,.2f}"
            printTailer(sp, totalValue + ' (Excluding BEKE)')
        print(padsp(' ', 31) + f'STOCK QUOTES from {database}.stock_quotes LOADED at {chunk[6].load_time}')
if __name__=="__main__": main()
