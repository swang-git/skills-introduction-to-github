#!/usr/bin/env python3
import sys, os, time, re
from bs4 import BeautifulSoup
from StockItem import Stock
from FundItem import Fund
from AnnuityItem import Annuity
from Utils import padsp, TeeToFileAndScreen, printHeader, printTailer

# nsp = 80 # very right
# nsp = 60 # middle
# nsp = 50
nsp = 0
sp = padsp('  ', nsp)

def loadStocks(isSaveToDB=False, run='stocks'):
    if run != 'both' and run != 'allm': return
    # startm = time.time()
    # symbols = ['T', 'CHTR', 'MSFT', 'CSCO', 'DELL', 'MSI']
    symbols = ['BEKE', 'T', 'CHTR', 'MSFT', 'CSCO', 'DELL']
    # symbols = ['MSFT']
    for symbol in symbols:
        stock = Stock(isSaveToDB, symbol)
        stock.getQuote()
        stock.display(sp)
        stock.saveToDB()
    # showFooter(sp, startm)

def loadFunds(isSaveToDB=False, run='funds'):
    if run != 'both' and run != 'funds'and run != 'allm': return
    today = time.strftime('%Y-%m-%d')
    now = today + time.strftime(' %H:%M:%S')
    showFundTimeLo = today + ' 17:00:00'
    showFundTimeHi = today + ' 18:00:00'

    if run == 'funds' or run == 'allm' or showFundTimeLo <= now <= showFundTimeHi:
        startm = time.time()
        printHeader(sp)
        symbols = ['FFTWX', 'FSKAX', 'FXAIX']
        # symbols = ['FFTWX']
        for symbol in symbols:
            fund = Fund(isSaveToDB, symbol)
            fund.getQuote()
            fund.display(sp)
            fund.saveToDB()
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

# ===== main ======
# printHeader()
# printTailer(1450.99)
# sys.exit(0)

logFile = '/home/swang/tmp/logs/sc/loadsec' + '_' + time.strftime('%a%H%M') + '.log' 
print('the log file is', logFile)
tee = TeeToFileAndScreen(logFile, 'w')
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
    loadStocks(args.isSaveToDB, args.run)
    printTailer(sp, round(time.time()-startm, 2))

if args.run == 'allm' or args.run == 'both' or args.run == 'funds':
    # startm = time.time()
    # printHeader(sp)
    loadFunds(args.isSaveToDB, args.run)
    # printTailer(sp, round(time.time()-startm, 2))

# anx = Annuity(0, 'FMPCC')
# anx.getQuote()
tee.close()
