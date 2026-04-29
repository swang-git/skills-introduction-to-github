#!/Users/swang/myenv/bin/python
import sys
from datetime import datetime, timedelta, date
import argparse
from sqlalchemy import func
from more_itertools import chunked

from Utils import printHeader, printTailer, displaySec, padsp
from Models import dbsession, MyPortfolio
from constants import COMPAN_LEN, NUM_PORTFOLIO_SEC
from sty import ef, rs, FgRegister
fg = FgRegister()

parser = argparse.ArgumentParser()
parser.add_argument("sub_days", metavar='int', type=int, nargs='?', default='0', help='sub days from today(must be < 0), default 0 for today')
# optional arguments
parser.add_argument('-d', '--db', type=str, default='prod', help='check quotes in this database default database: prod')
# parser.add_argument('-d', '--db', type=str, default='devx', help='check quotes in this database default database: devx')
args = parser.parse_args()
sub_days = args.sub_days
if sub_days > 0:
    print("sub_days must negative, %s given, exiting..."%sub_days)
    sys.exit(1)
database = args.db
# print("sub_days=%d db=%s"%(sub_days, database))

def reorder(rows) :
    symbols = ['T', 'WBD', 'CHTR', 'DELL', 'CSCO', 'MSFT']
    order_map = {v: i for i, v in enumerate(symbols)}
    data_sorted = sorted(rows, key=lambda o: order_map.get(o.symbol, len(symbols)))
    return data_sorted

def get_last_2_set_stock_quotes(start):
    rows = dbsession(database).query(MyPortfolio).order_by((MyPortfolio.asof_time).desc(), MyPortfolio.account.desc())\
    .limit(2*NUM_PORTFOLIO_SEC).offset(-sub_days*NUM_PORTFOLIO_SEC).all()
    rowstdy = rows[0:NUM_PORTFOLIO_SEC]
    rowsyst = rows[NUM_PORTFOLIO_SEC:]


    return rowsyst, rowstdy

def XXXget_shares(): return {'T': 287, 'WBD': 69, 'CHTR':20, 'DELL':36, 'CSCO':640, 'MSFT':400}

def print_rows_prior_day(rows):
    sp = ""
    num_of_stocks = len(rows)
    for chunk in chunked(rows, num_of_stocks):
        # if len(chunk) <= 0:break
        totalValue = 0
        printHeader(sp)
        for row in chunk:
            displaySec(row, sp)
            totalValue += float(row.current_value)
        totalValue = f"{totalValue:,.2f}"
        dday = rows[0].asof_time.strftime("%Y-%m-%d")
        printTailer(sp, totalValue, f'Date: {dday}', padsp(sp, 60 + COMPAN_LEN) + f'{database}.my_portfolios')

def print_rows(rows, cdiff, spgap):
    # symbols = ['T', 'WBD', 'CHTR', 'DELL', 'CSCO', 'MSFT']
    # [print("total_gl=[%s]"%x.total_gl) for x in rows]
    sp = ""
    # shares = get_shares()
    # num_of_stocks = len(symbols)
    num_of_stocks = len(rows)
    for chunk in chunked(rows, num_of_stocks):
        totalValue = 0
        printHeader(sp)
        for row in chunk:
            # quantity = shares[row.symbol]
            # portfolio = Portfolio(row, quantity)
            displaySec(row, sp)
            if row.symbol == 'BEKE':
                continue
            else:
                totalValue += float(row.current_value)
        totalValue = f"{totalValue:,.2f}"
        # load_time = rows[5].asof_time
        # dday = load_time.strftime('%Y-%m-%d (%a)')
        dday = rows[0].asof_time.strftime("%Y-%m-%d")
        printTailer(sp, totalValue, f"Date: {dday} G/L: {cdiff}", spgap + f'{database}.my_portfolios')

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

    # shares = get_shares()
    # rowsy = reorder(rowsyst)
    # print([row.print_pretty() for row in rowsy])
    # rowst = reorder(rowstdy)
    # print(rowst[0].print_pretty())

    rowsy = rowsyst
    rowst = rowstdy

    # totaly = sum(row.price * shares[row.symbol] for row in rowsy)
    # totalt = sum(row.price * shares[row.symbol] for row in rowst)
    # totaly = sum(row.price * row.quantity for row in rowsy)
    # totalt = sum(row.price * row.quantity for row in rowst)
    totaly = sum(row.current_value for row in rowsy)
    totalt = sum(row.current_value for row in rowst)
    diff1 = totalt - totaly # totalValue of current day - totalValue of prior day
    # diff2 = sum(row.price_change * shares[row.symbol] for row in rowst) # based on current day
    diff2 = sum(row.price_change * row.quantity for row in rowst) # based on current day
    print_rows_prior_day(rowsy)
    
    cdiff1, difflen1 = get_formated_data(diff1)
    cdiff2, difflen2 = get_formated_data(diff2)
    cdiff = cdiff1 + ' diff w/ prvday ~ ' + cdiff2 + ' w/ curday pchange'
    spc = ' '    
    tlen = 18
    # COMPAN_LEN = 18
    sps = tlen - difflen1 - difflen2 + COMPAN_LEN
    spgap = sps*spc
    # print(f"tlen=[{tlen}] difflen1=[{difflen1}] difflen2=[{difflen2}] sps=[{sps}]")
    print_rows(rowst, cdiff, spgap)
    print(' ╚' + (126 + COMPAN_LEN)*'═' + '╝')
    # print("total value %s for prvday"%totaly)
    # print("total value %s for theday"%totalt)
    sys.exit(0)
