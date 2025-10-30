import sys, os, time
sys.path.insert(0, os.path.join('/sites/projects/load-market'))
# sys.path.insert(0, '/sites/projects/load-market')
# print(sys.path)
from LM_Utils import padsp, boldit, TeeToFileAndScreen, printHeader, printTailer, displaySec
from LM_DB import loadQuantity, saveSecToDB
from FundItem import Fund
from StockItem import Stock

class Security:
    def __getattr__(self, key): return None
    def __init__(self, isSaveToDB, funds, stocks):
        self.lnk = None
        self.isSaveToDB = isSaveToDB
        self.funds = funds
        self.stocks = stocks
        self.quanlst = loadQuantity()
        self.nsp = 0
        self.sp = padsp('  ', self.nsp)

    def loadFund(self):
        startm = time.time()
        printHeader(self.sp)
        for symbol in self.funds:
            fund = Fund(self.isSaveToDB, symbol, self.quanlst[symbol])
            fund.getQuote()
            displaySec(fund, self.sp)
            if self.isSaveToDB: saveSecToDB(fund)
        printTailer(self.sp,  round(time.time()-startm, 2))

    def loadStock(self):
        startm = time.time()
        printHeader(self.sp)
        for symbol in self.stocks:
            stck = Stock(self.isSaveToDB, symbol, self.quanlst[symbol])
            stck.getQuote()
            displaySec(stck, self.sp)
            if self.isSaveToDB: saveSecToDB(stck)
        printTailer(self.sp,  round(time.time()-startm, 2))