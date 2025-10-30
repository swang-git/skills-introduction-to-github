from bs4 import BeautifulSoup, Comment
import sys
import requests_html
# import lxml.html as lh
from Utils import dlout, boldit
from Utils import padsp, isfloat
from Utils import pedsp, colorShow
from datetime import datetime, date, time
from urllib.request import Request, urlopen
from Fund52WeekHiLow import FundHiLow
from DB import getLastPrice, addStockPrice
# from sty import fg, bg, ef, rs, RgbFg

class Fund:
    def __getattr__(self, key): return None
    def __init__(self, isSaveToDB, symbol, quantity):
        self.lnk = None
        self.isSaveToDB = isSaveToDB
        self.symbol = symbol
        self.price = 0.0
        self.quantity = quantity
        self.lo52w = None
        self.hi52w = None
        self.change = 0.0
        self.status = 'A'
        self.lastupd = None
        self.intradayChange = ''
        self.intradayPrice = ''

    def getQuote(self):
        # self.lnk = 'https://finance.yahoo.com/quote/' + self.symbol + '?q=' + self.symbol
        # print('[%s]'%self.symbol)
        self.lnk = "https://in.finance.yahoo.com/quote/" + self.symbol
        # self.lnk = 'file:///home/swang/tmp/' + self.symbol + '.html'
        # print('open url', self.lnk)
        # headers = {'User-Agent': "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36"}
        session = requests_html.HTMLSession()
        try:
            r = session.get(self.lnk)
            self.soup = BeautifulSoup(r.content, 'lxml')
            # req = Request(self.lnk, None, headers = headers)
            # page = urlopen(req).read()
            # self.soup = BeautifulSoup(page,  "html.parser").find('body')
            # print(self.soup.prettify())

        except Exception as e:
            print('WW getInfo Exception, return None and the error is:', e)
            return None

        # prc = self.soup.select('span[data-reactid="33"]')
        # pch = self.soup.select('span[data-reactid="34"]')
        prc = self.soup.select('span[data-reactid="32"]')
        pch = self.soup.select('span[data-reactid="33"]')
        # print('prc:%s'%prc)
        # print('pch:%s'%pch)
        try:
            prc = prc[1].get_text()
            # print('====XXXX===pc', prc)
            if isfloat(prc): self.price = prc
            else: self.price = prc[0].get_text()
        except Exception as e:
            self.price = prc[0].get_text()
        try:
            chg = pch[1].get_text().split(' ')[0]
            if isfloat(chg): self.change = chg
            else: self.change = pch[0].get_text().split(' ')[0]
        except Exception as e: 
            self.change = pch[1].get_text().split(' ')[0]
        self.lastupd = datetime.now().strftime('%Y-%m-%d %H:%M:%S')

        fundHiLow = FundHiLow(self.symbol)
        [self.lo52w, self.hi52w] = fundHiLow.get52WeekHiLow()
        
    def display(self, sp):
        lprice = getLastPrice(self.symbol)
        if lprice is None:
            print('Can not get last price for', sec.symbol, 'exiting ...')
            sys.exit(100)
        tag = 'x '
        change = float(self.price) - float(lprice)
        if change > 0: tag = '+'
        elif change == 0: tag = '='
        elif change < 0: tag = '-'
        self.tag = tag
        colorShow(sp, 'Funds', self)
    
    def saveToDB(self):
        if self.isSaveToDB: addStockPrice(self)
