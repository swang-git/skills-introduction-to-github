#!/usr/bin/env python3
import sys, os, time, re
from bs4 import BeautifulSoup
from StockItem import Stock
from FundItem import Fund
# from AnnuityItem import Annuity
from LM_Utils import TeeToFileAndScreen, printHeader, printTailer, displaySec, padsp
from LM_DB import loadQuantity, saveSecToDB
# from LM_Utils import dlout, boldit, padsp, isfloat, pedsp, colorShow
# from LM_Utils import dlout, padsp, pedsp, boldit, isfloat, colorShow

# nsp = 80 # very right
# nsp = 60 # middle
# nsp = 50
nsp = 0
sp = padsp('  ', nsp)

def loadStocks(qty, isSaveToDB=False, run='stocks'):
    if run != 'both' and run != 'allm': return
    # startm = time.time()
    # symbols = ['DELL']
    symbols = ['BEKE', 'CHTR', 'CSCO', 'DELL', 'MSFT', 'T']
    for symbol in symbols:
        quantity = qty[symbol]
        stock = Stock(isSaveToDB, symbol, quantity)
        stock.getQuote()
        displaySec(stock, sp)
        if isSaveToDB: saveSecToDB(stock)
    # showFooter(sp, startm)

def loadFunds(qty, isSaveToDB=False, run='funds'):
    if run != 'both' and run != 'funds'and run != 'allm': return
    today = time.strftime('%Y-%m-%d')
    now = today + time.strftime(' %H:%M:%S')
    showFundTimeLo = today + ' 07:00:00'
    showFundTimeHi = today + ' 23:59:00'

    if run == 'funds' or run == 'allm' or showFundTimeLo <= now <= showFundTimeHi:
        startm = time.time()
        printHeader(sp)
        # symbols = ['FFTWX']
        symbols = ['FFTWX', 'FSKAX', 'FXAIX']
        for symbol in symbols:
            quantity = qty[symbol]
            fund = Fund(isSaveToDB, symbol, quantity)
            fund.getQuote()
            displaySec(fund, sp)
            if isSaveToDB: saveSecToDB(fund)
        printTailer(sp,  round(time.time()-startm, 2))
    else:
        print(sp, '>>>>>>>>>>> FUND QUOTES WILL BE LOADED BETWEEN: %s and %s <<<<<<<<<<<'%(showFundTimeLo, showFundTimeHi[11:]))

import argparse
def my_argparse():
    parser = argparse.ArgumentParser(description='load stocks/funds quotes in my portfolio')
    parser.add_argument("run", metavar='str', type=str, nargs='?', default='both', help='both: load quotes for both w/ time restrictions;\
        funds: for funds; allm: run both w/o time restrictions, default to both')
    parser.add_argument("isSaveToDB", metavar='int', type=int, nargs='?', default=1, help='1: to save to DB; 0: not; default to 1')
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
quantities = loadQuantity()
# printHeader()
# printTailer(1450.99)
# sys.exit(0)

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
if args.isSaveToDB != 0 and args.isSaveToDB != 1:
    print('unknown option: %s, it must be 0 or 1'%args.isSaveToDB)
    sys.exit(0)
if args.run != 'both' and args.run != 'allm' and args.run != 'funds':
    print('unknown option: %s, it must be both or stocks or funds'%args.run)
    sys.exit(0)

if args.run == 'allm' or args.run == 'both' or args.run == 'stocks':
    startm = time.time()
    printHeader(sp)
    loadStocks(quantities, args.isSaveToDB, args.run)
    printTailer(sp, round(time.time()-startm, 2))

if args.run == 'allm' or args.run == 'both' or args.run == 'funds':
    # startm = time.time()
    # printHeader(sp)
    loadFunds(quantities, args.isSaveToDB, args.run)
    # printTailer(sp, round(time.time()-startm, 2))

# anx = Annuity(0, 'FMPCC')
# anx.getQuote()
tee.close()
