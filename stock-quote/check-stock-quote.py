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
parser.add_argument("sub_days", metavar='int', type=int, nargs='?', default='0', help='sub days from today(must be < 0), default 0 for today')
# optional arguments
parser.add_argument('-d', '--db', type=str, default='prod', help='check quotes in this database default database: prod')
args = parser.parse_args()
sub_days = args.sub_days
if sub_days > 0:
    print("sub_days must negative, %s given, exiting..."%sub_days)
    sys.exit(1)
database = args.db
print("sub_days=%d db=%s"%(sub_days, database))

def reorder(rows) :
    symbols = ['T', 'WBD', 'CHTR', 'DELL', 'CSCO', 'MSFT']
    order_map = {v: i for i, v in enumerate(symbols)}
    data_sorted = sorted(rows, key=lambda o: order_map.get(o.symbol, len(symbols)))
    return data_sorted

def get_last_2_set_stock_quotes(start):
    rows = dbsession(database).query(StockQuote).order_by(StockQuote.load_time.desc()).offset(-sub_days*6).limit(12).all()
    rowstdy = rows[0:6]   #last set (like today's set if sub_days = 0) since sub_days
    rowsyst = rows[6:]    #last 2nd set (like yesterday's or the last 2nd set) if sub_days = 0
    return rowsyst, rowstdy

def get_shares(): return {'T': 287, 'WBD': 69, 'CHTR':20, 'DELL':36, 'CSCO':640, 'MSFT':400}

def print_rows_prior_day(rows):
    symbols = ['T', 'WBD', 'CHTR', 'DELL', 'CSCO', 'MSFT']
    sp = ""
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
        printTailer(sp, totalValue, f'Date: {dday}', padsp(sp, 58) + f'{database}.stock_quotes')

def print_rows(rows, cdiff, spgap):
    symbols = ['T', 'WBD', 'CHTR', 'DELL', 'CSCO', 'MSFT']
    sp = ""
    shares = get_shares()
    num_of_stocks = len(symbols)
    for chunk in chunked(rows, num_of_stocks):
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
        printTailer(sp, totalValue, f"Date: {dday} G/L: {cdiff}", spgap + f'{database}.stock_quotes')

def get_formated_data(diff):
    sdiff = f"{diff:,.2f}"  #currency format like 2,550.45
    difflen = len(str(sdiff))
    sdiffstr = str(sdiff)
    fgcolor = fg.red if diff < 0 else (fg.yellow if diff == 0 else fg.green)
    if diff < 0: sdiffstr = str(sdiff)[1:]; difflen -= 1
    cdiff =  ef.bold + fgcolor + sdiffstr + fg.rs + rs.bold_dim
    return cdiff, difflen
    
if __name__=="__main__":
    rowsyst, rowstdy = get_last_2_set_stock_quotes(sub_days)
    # for row in rowsyst: print(row.load_time, (row.load_time).strftime('%a'), row.symbol)
    # for row in rowstdy: print(row.load_time, (row.load_time).strftime('%a'), row.symbol)

    shares = get_shares()
    rowsy = reorder(rowsyst)
    rowst = reorder(rowstdy)

    totaly = sum(row.price * shares[row.symbol] for row in rowsy)
    totalt = sum(row.price * shares[row.symbol] for row in rowst)
    diff1 = totalt - totaly # totalValue of current day - totalValue of prior day
    diff2 = sum(row.price_change * shares[row.symbol] for row in rowst) # based on current day
    print_rows_prior_day(rowsy)
    
    cdiff1, difflen1 = get_formated_data(diff1)
    cdiff2, difflen2 = get_formated_data(diff2)
    cdiff = cdiff1 + ' diff w/ prvday ~ ' + cdiff2 + ' w/ curday pchange'
    spc = ' '    
    tlen = 16
    spgap = (tlen - difflen1 - difflen2)*spc
    print_rows(rowst, cdiff, spgap)
    print(' ╚' + 128*'═' + '╝')
    sys.exit(0)
