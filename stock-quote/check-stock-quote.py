#!/usr/bin/python
import sys
from datetime import datetime, timedelta
import argparse
from sqlalchemy import func
from more_itertools import chunked

from Utils import printHeader, printTailer, displaySec, padsp
from Models import dbsession, StockQuote, Portfolio
from sty import ef, rs, FgRegister
fg = FgRegister()

parser = argparse.ArgumentParser()
parser.add_argument("add_days", metavar='int', type=int, nargs='?', default='0', help='add/sub days from today, default 0 for today')
# optional arguments
parser.add_argument('-d', '--db', type=str, default='prod', help='check quotes in this database default database: prod')
args = parser.parse_args()
add_days = args.add_days
database = args.db

def reorder(rows) :
    symbols = ['T', 'WBD', 'CHTR', 'DELL', 'CSCO', 'MSFT']
    order_map = {v: i for i, v in enumerate(symbols)}
    data_sorted = sorted(rows, key=lambda o: order_map.get(o.symbol, len(symbols)))
    return data_sorted

def get_stock_quotes(last_day=None):
    if last_day == None: 
        return dbsession(database).query(StockQuote).order_by(StockQuote.load_time.desc()).limit(6).all()

    backRange = 100
    for add_days in range(1, backRange):
        load_date = (datetime.now() + timedelta(days=-add_days)).strftime('%Y-%m-%d')
        if (load_date >= last_day): continue
        # print("load_date=%s last_day=%s comp=%s"%(load_date, last_day, load_date<=last_day))
        rows = dbsession(database).query(StockQuote)\
            .filter(func.date_format(StockQuote.load_time, '%Y-%m-%d').label('formated_date')==load_date)\
            .order_by(StockQuote.load_time.desc()).limit(6).all()
        if (len(rows) == 6): return rows
    print("There are not quotes for %d days back from %s"%(backRange, last_day))
    sys.exit(1)

def get_shares(): return {'T': 287, 'WBD': 69, 'CHTR':20, 'DELL':36, 'CSCO':640, 'MSFT':400}

def print_rows(rows, diff=None):
    symbols = ['T', 'WBD', 'CHTR', 'DELL', 'CSCO', 'MSFT']
    sp = ''
    shares = get_shares()
    num_of_stocks = len(symbols)
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
        load_time = rows[5].load_time
        dday = load_time.strftime('%Y-%m-%d (%a)')
        if (diff==None): printTailer(sp, totalValue, f'Date: {dday}', padsp(sp, 58) + f'{database}.stock_quotes')
        else: 
            if diff == 0: cdiff = fg.yellow + diff + fg.rs
            elif diff > 0: cdiff = fg.green + diff + fg.rs
            else:
                diff = str(diff)[1:]
                cdiff = fg.red + diff + fg.rs
            cdiff = ef.bold + cdiff + rs.bold_dim
            printTailer(sp, totalValue, f'Date: {dday} G/L={cdiff}', padsp(sp, 54 - len('$' + str(diff))) + f'{database}.stock_quotes')
        

def main():
    shares = get_shares()

    rows = get_stock_quotes()
    rows1 = reorder(rows)
    last_load_day = rows1[5].load_time.strftime('%Y-%m-%d')
    total1 = sum(row.price * shares[row.symbol] for row in rows1)
    # print("total1=%s"%total1)
    # for row in rows1: print("load_time=[%s] symbol=[%s]"%(row.load_time, row.symbol))
    
    rows = get_stock_quotes(last_load_day)
    rows2 = reorder(rows)
    total2 = sum(row.price * shares[row.symbol] for row in rows2)
    # print("total2=%s"%total2)
    diff = total1 - total2
    diff = f'{diff:,.2f}'
    diff = float(diff)
    print_rows(rows2)
    print_rows(rows1, diff)
    print(' ╚' + 128*'═' + '╝')

if __name__=="__main__": main()
