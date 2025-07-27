# from bs4 import BeautifulSoup, Comment
import sys, time
from datetime import datetime
# from urllib.request import Request, urlopen
# import requests_html
# import lxml.html as lh

from Utils import isfloat
# from LM_DB import getLastPrice, addStockPrice
# from LM_Utils import dlout, padsp, pedsp, boldit, isfloat, colorShow

class Stock:
    def __getattr__(self, key): return None
    # def __str__(self): return str(self.__class__) + ": " + str(self.__dict__)
    def __str__(self):
        return  str(self.__class__) + '\n'+ '\n'.join(('{} = {}'.format(item, self.__dict__[item]) for item in self.__dict__))

    def __init__(self, symbol, quantity):
        # self.isSaveToDB = isSaveToDB
        self.symbol = symbol
        self.price = None
        self.quantity = quantity
        self.price_change = None
        self.low_52_week = None
        self.high_52_week = None
        self.lastupd = datetime.now().strftime('%Y-%m-%d %H:%M') + ':00'
        self.day_high = None
        
    def dout(self, type, sym, pa, pi):
        print('\n--- Can NOT get %s for %s\n pa_len:%s, pa:%s\n pi_len:%s, pi:%s\n'%(type, self.symbol, len(pa), pa, len(pi), pi))
    def dxout(self, type, pa):
        print('\n--- Can NOT get %s for %s:\n pa_len:%s, pa:%s\n'%(type, self.symbol, len(pa), pa))

    def getQuote(self, info):
        price = info.get('close')
        if price == None: price = info['currentPrice']
        self.price = price
        self.open = info['open']
        self.prev_close = info['regularMarketPreviousClose']
        self.price_change = price - self.prev_close
        self.day_low = info['dayLow']
        self.day_high = info['dayHigh']
        self.low_52_week = info['fiftyTwoWeekLow']
        self.high_52_week = info['fiftyTwoWeekHigh']
