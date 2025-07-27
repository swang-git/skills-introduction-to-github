#!/usr/bin/env python3
import sys, os, time, re
from Utils import colorShow, TeeToFileAndScreen, printHeader, printTailer, displayStock, padsp
from DB import loadQuantity, saveSecToDB
import yfinance as yf
from Stock import Stock 

# nsp = 80 # very right
# nsp = 60 # middle
# nsp = 50
nsp = 0
sp = padsp('  ', nsp)

symbols = ['MSFT', 'CSCO', 'DELL', 'CHTR', 'T', 'WBD', 'BEKE', 'AAPL', 'GOOG', 'AMZN', 'TSLA', 'NVDA']

quantities = loadQuantity()
quantities['AAPL'] = 0
quantities['GOOG'] = 0
quantities['AMZN'] = 0
quantities['TSLA'] = 0
quantities['NVDA'] = 0
quantities['WBD'] = 69.000
quantities['BEKE'] = 100.000

def loadStocks(quantity, run='stocks'):
    # print("-fn-loadStocks")
    if run != 'stocks': return
    UNIFIEDLOADTIME = datetime.now().strftime('%Y-%m-%d %H:%M') + ':00'
    stocks = []
    for symbol in symbols:
        quantity = quantities[symbol]
        dat = yf.Ticker(symbol)
        stock = Stock(symbol, quantity)
        stock.getQuote(dat.info)
        stock.lastupd = UNIFIEDLOADTIME  ## unified loading time
        displayStock(stock, sp)
        stocks.append(stock)
    # sys.exit(0)
    return stocks

import argparse
def my_argparse():
    parser = argparse.ArgumentParser(description='load stocks/funds quotes in my portfolio')
    parser.add_argument("load", metavar='str', type=str, nargs='?', default='stocks', help='both: load quotes for both w/ time restrictions;\
        funds: for funds; stocks: run both w/o time restrictions, default to both')
    parser.add_argument("saveToDB", metavar='int', type=int, nargs='?', default=0, help='1: to save to DB; 0: not; default to 1')
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

# def resetLoadTime(stocks):
#     # unifiedLoadTime = datetime.now().strftime('%Y-%m-%d %H:%M') + ':00'
#     UNIFIEDLOADTIME = datetime.now().strftime('%Y-%m-%d %H') + ':00:77'
#     for stock in stocks: stock.lastupd = UNIFIEDLOADTIME

# ===== main ====== 
logFile = '/home/swang/tmp/logs/sc/loadsec' + '_' + time.strftime('%a') + '.log'
# tstFile = '/home/swang/tmp/logs/sc/BAK/loadsec_Thu2230.log'
# tstFile = '/home/swang/tmp/ArtsHome.vue'
if isCreatedLastWeek(logFile):
    print(logFile, 'was created last week, truncate it 0 size')
    f = open(logFile, "w")
    f.truncate()
    f.close()
# sys.exit(0)
# print(logFile, 'is not created before today, truncate it 0 size')
# f = open(logFile, "w")
# f.truncate()
# f.close()

print('appending new data to %s'%logFile)
tee = TeeToFileAndScreen(logFile, 'a')
args = my_argparse()
if args.saveToDB != 0 and args.saveToDB != 1:
    print('unknown option: %s, it must be 0 or 1'%args.saveToDB)
    sys.exit(0)
if args.load != 'both' and args.load != 'stocks' and args.load != 'funds':
    print('unknown option: %s, it must be both or stocks or funds'%args.load)
    sys.exit(0)

# if args.load == 'stocks' or args.load == 'both' or args.load == 'stocks':
if args.load == 'stocks':
    startm = time.time()
    printHeader(sp)
    stocks = loadStocks(quantities, args.load)
    printTailer(sp, round(time.time()-startm, 2))
    # print(stocks)
    print('save to table stock_quotes')
    if (args.saveToDB):
        for stock in stocks: saveSecToDB(stock)
tee.close()
