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
    rows12 = dbsession(database).query(StockQuote).order_by(StockQuote.load_time.desc()).offset(-sub_days*6).limit(12).all()
    rowstdy = rows12[0:6]     #last set since sub_days
    rowsyst = rows12[6:12]    #last 2nd set like today or the last set if sub_days = 0
    return rowsyst, rowstdy

# def get_stock_quotes(load_day):
#     # if load_day == None:
#     #     return dbsession(database).query(StockQuote).order_by(StockQuote.load_time.desc()).limit(6).all()
    
#     print("load_day=%s sub_days=%s"%(load_day, sub_days))
#     rows = dbsession(database).query(StockQuote)\
#         .filter(func.date_format(StockQuote.load_time, '%Y-%m-%d').label('formated_date')==load_day)\
#         .order_by(StockQuote.load_time.desc()).limit(6).all()
#     if (len(rows) == 6): return rows

#     print("loop for previous days, i.e. < %s"%load_day)
#     backRange = 100
#     for add_days in range(1, backRange):
#         prev_day = (datetime.now() + timedelta(days=-add_days)).strftime('%Y-%m-%d')
#         if (prev_day >= load_day): continue
#         print("prev_day=%s load_day=%s comp=%s"%(prev_day, load_day, prev_day<=load_day))
#         rows = dbsession(database).query(StockQuote)\
#             .filter(func.date_format(StockQuote.load_time, '%Y-%m-%d').label('formated_date')==prev_day)\
#             .order_by(StockQuote.load_time.desc()).limit(6).all()
#         if (len(rows) == 6): return rows
#     print("There are not quotes for %d days back from %s"%(backRange, load_day))
#     sys.exit(1)

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
            sdiff = f"{diff:,.2f}"  #currency format like 2,550.45
            if diff == 0: diff='0.00'; cdiff = ef.bold + fg.yellow + str(sdiff) + fg.rs + rs.bold_dim
            elif diff > 0: cdiff = ef.bold + fg.green + str(sdiff) + fg.rs + rs.bold_dim
            else:
                diff = str(sdiff)[1:]
                cdiff =  ef.bold + fg.red + str(sdiff)[1:] + fg.rs + rs.bold_dim
                # print("diff=%s cdiff=%s sdiff=%s"%(diff, cdiff, sdiff))
            printTailer(sp, totalValue, f'Date: {dday} G/L={cdiff}', padsp(sp, 54 - len('$' + str(diff))) + f'{database}.stock_quotes')
        
if __name__=="__main__":
    rowsyst, rowstdy = get_last_2_set_stock_quotes(sub_days)
    # for row in rowsyst: print(row.load_time, (row.load_time).strftime('%a'), row.symbol)
    # for row in rowstdy: print(row.load_time, (row.load_time).strftime('%a'), row.symbol)

    shares = get_shares()
    rowsy = reorder(rowsyst)
    rowst = reorder(rowstdy)

    totaly = sum(row.price * shares[row.symbol] for row in rowsy)
    totalt = sum(row.price * shares[row.symbol] for row in rowst)
    diff = totalt - totaly
    print_rows(rowsy)
    print_rows(rowst, diff)
    print(' ╚' + 128*'═' + '╝')
    sys.exit(0)

    # load_day = (datetime.now() + timedelta(days=sub_days)).strftime('%Y-%m-%d')
    # # print("load_date=%s"%load_date)
    # rows = get_stock_quotes(load_day)
    # rows1 = reorder(rows)
    # last_load_day = rows1[5].load_time

    # total1 = sum(row.price * shares[row.symbol] for row in rows1)
    # # print("total1=%s"%total1)
    # # for row in rows1: print("load_time=[%s] symbol=[%s]"%(row.load_time, row.symbol))
    
    # load_day = (last_load_day + timedelta(days=-1)).strftime('%Y-%m-%d')
    # rows = get_stock_quotes(load_day)
    # rows2 = reorder(rows)
    # total2 = sum(row.price * shares[row.symbol] for row in rows2)
    # diff = total1 - total2
    # # print("total1=%f total2=%f diff=%f diffx=%f"%(total1, total2, diff, total1-total2))
    # print_rows(rows2)
    # print_rows(rows1, diff)
    # print(' ╚' + 128*'═' + '╝')
