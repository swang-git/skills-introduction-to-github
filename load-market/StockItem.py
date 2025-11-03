from bs4 import BeautifulSoup, Comment
import sys, time
from datetime import datetime
from urllib.request import Request, urlopen
import requests_html
import lxml.html as lh

from LM_Utils import isfloat
# from LM_DB import getLastPrice, addStockPrice
# from LM_Utils import dlout, padsp, pedsp, boldit, isfloat, colorShow

class Stock:
    def __getattr__(self, key): return None
    def __init__(self, isSaveToDB, symbol, quantity):
        self.lnk = None
        self.isSaveToDB = isSaveToDB
        self.symbol = symbol
        self.price = 0.0
        self.quantity = quantity
        self.change = 0.0
        self.lo52w = 0.0
        self.hi52w = 0.0
        self.lastupd = None
        self.status = 'A'
        self.intradayChange = '' # A for after market
        self.intradayPrice = ''  # A for after market

    def dout(self, type, sym, pa, pi):
        print('\n--- Can NOT get %s for %s\n pa_len:%s, pa:%s\n pi_len:%s, pi:%s\n'%(type, self.symbol, len(pa), pa, len(pi), pi))
    def dxout(self, type, pa):
        print('\n--- Can NOT get %s for %s:\n pa_len:%s, pa:%s\n'%(type, self.symbol, len(pa), pa))

    def DEBUG_chk_tds(self):
        tds = self.soup.find_all('td')
        for td in tds: print(td)
        sys.exit(0)

    def getQuote(self):
        # self.lnk = 'https://in.finance.yahoo.com/quote/' + self.symbol
        self.lnk = 'https://finance.yahoo.com/quote/' + self.symbol
        session = requests_html.HTMLSession()
        r = session.get(self.lnk)
        self.soup = BeautifulSoup(r.content, 'lxml')
        idmap = dict()
        for spn in self.soup.find_all('span'):
            # print(spn); sys.exit(0)
            if not isfloat(spn.get_text().split(' ')[0]): continue
            idmap[int(spn['data-reactid'])] = float(spn.get_text().split(' ')[0])
        # self.price = idmap[32]; self.change = idmap[33]
        self.price = idmap[49]; self.change = idmap[50]
        for tdx in self.soup.find_all('td'):
            if tdx.get('data-test') == "FIFTY_TWO_WK_RANGE-value":
                x = tdx.get_text().split(' - ')
                self.lo52w = x[0]
                self.hi52w = x[1]
        self.lastupd = datetime.now().strftime('%Y-%m-%d %H:%M:%S')

    # def display(self, sp):
    #     # print('symbol:%s, price:%s, change:%s, lo52w:%s, hi52w:%s'%(self.symbol, self.price, self.change, self.lo52w, self.hi52w))
    #     lprice = getLastPrice(self.symbol)
    #     if lprice is None:
    #         print('Can not get last price for', self.symbol, 'exiting ...')
    #         sys.exit(100)
    #     # print(lprice, sec.price)
    #     tag = 'x'
    #     change = float(self.price) - float(lprice)
    #     # print('change', change, lprice, sec.price, change > 0.0, change == 0, change < 0)
    #     if change > 0: tag = '+'
    #     elif change == 0: tag = '='
    #     elif change < 0: tag = '-'
    #     self.tag = tag
    #     colorShow(sp, 'Stock', self)
    #     # self.show()

    # def saveToDB(self):
        # print('isSaveToDB', self.isSaveToDB)
        # if self.isSaveToDB: addStockPrice(self)