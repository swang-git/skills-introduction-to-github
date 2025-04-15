#!/usr/bin/env python3
import sys, os, time, re
# from bs4 import BeautifulSoup
from Models import StockQuote
from StockItem import Stock
import sys, os, time

# import json
import argparse
from Models import StockQuote

import argparse
def my_argparse():
    parser = argparse.ArgumentParser(description='load stocks/funds quotes from website')
    parser.add_argument("symbol", metavar='str', type=str, nargs='?', default='MSFT', help='load today quote for it')
    return parser.parse_args()

from datetime import date
from datetime import datetime
def isCreatedLastWeek(fname):
    if not os.path.exists(fname): return False
    createdtime = time.ctime(os.path.getctime(fname))
    cdate = datetime.strptime(createdtime, '%a %b %d %H:%M:%S %Y').date()
    today = date.today()
    # print(cdate, today, cdate < today)
    return cdate < today
# ===== main ======
symbols = ['WBD', 'BEKE', 'CHTR', 'CSCO', 'DELL', 'MSFT', 'T']
if __name__=="__main__":
    argparser = argparse.ArgumentParser()
    argparser.add_argument('symbol', help = 'stock symbol')
    args = argparser.parse_args()
    symbol = args.symbol
    if symbol not in symbols:
        print('we are not loading for [%s] for now.'%symbol)
        print('try:', symbols)
        sys.exit(1)
    load_time = datetime.now().strftime('%Y-%m-%d %H:%M:%S')

    # print ("Save quote to stock_quotes table")
    stock = StockQuote(symbol)
    stock.getQuote()
    # stock.getQuote('testing')
    # stock.getFakeQuote()
    # stock.saveToDB()
    # print(json.dumps(stock.to_dict(), indent=2))

