from bs4 import BeautifulSoup, Comment
import sys, time
from Utils import dlout
from Utils import padsp
from Utils import pedsp
from Utils import boldit, isfloat, colorShow
from datetime import datetime
from urllib.request import Request, urlopen
from LM_DB import getLastPrice, addStockPrice
# from sty import fg, bg, ef, rs, RgbFg

class Annuity:
    def __getattr__(self, key): return None
    def __init__(self, isSaveToDB, symbol):
        self.lnk = None
        self.isSaveToDB = isSaveToDB
        self.symbol = symbol
        self.price = 0.0
        self.change = 0.0
        self.lo52w = 0.0
        self.hi52w = 0.0
        self.lastupd = None
        self.status = 'A'
        self.intradayChange = '' # A for after market
        self.intradayPrice = ''  # A for after market
    def setPrice(self):
        p55 = self.soup.select('span[data-reactid="55"]') # after market price
        p50 = self.soup.select('span[data-reactid="50"]') # intraday price
        # print(' --- debug >>>> p55:%s, p50:%s, len(p55):%s'%(p55, p50, len(p55)))
        if len(p55) < 0 or len(p50) < 0:
            print('debug >>>> len(p55):%s, len(p50):%s'%(len(p50), len(p55)))
            return # default to 0.0 as initialized
        else:
            px55 = p55[0].get_text()
            px50 = p50[0].get_text()
        # print(' --- debug >>>> px55:%s, px50:%s'%(px55, px50))
        if   isfloat(px55) == True:
            self.price = px55 # after market price
            self.intradayPrice = 'a' # after market price
        elif isfloat(px50) == True: self.price = px50 # intraday price
    
    def setChange(self):
        pm = self.soup.select('span[data-reactid="58"]') # after market
        # print('\n\n=========', len(pm), pm, '\n============\n\n')
        # if pm and len(pm) == 2 and "Trsdu(0.3s) Fw(500) Pstart(10px) Fz(24px)" in pm[0].get('class'):
        if pm and len(pm) >= 1: # and "Trsdu(0.3s) Fw(500) Pstart(10px) Fz(24px)" in pm[0].get('class'):
            self.intradayChange = 'a'
            self.change = pm[0].get_text().split(' ')[0]
        else:
            pm = self.soup.select('span[data-reactid="51"]') # intraday
            if pm and len(pm) == 2: self.change = pm[0].get_text().split(' ')[0]   # pm not empty
    
    def set52weekHiLow(self):
        pw = self.soup.select('tbody[data-reactid="93"]')[0].find_all('td')
        # print('--- debug>>>> price:%s, change:%s'%(self.price, self.change))
        if len(pw) <= 0: return # default to 0.0 as initialized
        for da in enumerate(pw):
            # if int(da[1]['data-reactid']) == 121 and 'FIFTY_TWO_WK_RANGE' in da[1]['data-test']:
            if da[1].get('data-test') is None: continue
            elif "FIFTY_TWO_WK_RANGE" in da[1].get('data-test'):
                hilowx = da[1].get_text().split(' - ')
                self.lo52w = hilowx[0]
                self.hi52w = hilowx[1]
                break

    def getQuote(self):
        self.lastupd = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        self.lnk = 'https://fundresearch.fidelity.com/annuities/view-all/' + self.symbol
        # print('open URL', self.lnk)
        headers = {'User-Agent': "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36"}
        try:
            req = Request(self.lnk, None, headers = headers)
            page = urlopen(req).read()
            # print(page)
            self.soup = BeautifulSoup(page, "html.parser").select(".data-card--content")
            # self.soup = BeautifulSoup(page, "html.parser").select('div[class="data-card--content"]')
            # self.soup = BeautifulSoup(page, "html.parser").find('div[class="data-card--content"]')
            self.soup = BeautifulSoup(page, "html.parser").find_all('div')
            # print(self.soup.prettify())
            print(self.soup)
            sys.exit(0)
        except Exception as e:
            print('WW getInfo Exception, return None and the error is:', e)
            return None
        
        self.setPrice()
        self.setChange()
        self.set52weekHiLow()
        # print('--- debug>>>> price:%s, change:%s'%(self.price, self.change))

    def display(self, sp):
        # print('symbol:%s, price:%s, change:%s, lo52w:%s, hi52w:%s'%(self.symbol, self.price, self.change, self.lo52w, self.hi52w))
        lprice = getLastPrice(self.symbol)
        if lprice is None:
            print('Can not get last price for', sec.symbol, 'exiting ...')
            sys.exit(100)
        # print(lprice, sec.price)
        tag = 'x'
        change = float(self.price) - float(lprice)
        # print('change', change, lprice, sec.price, change > 0.0, change == 0, change < 0)
        if change > 0: tag = '+'
        elif change == 0: tag = '='
        elif change < 0: tag = '-'
        self.tag = tag
        colorShow(sp, 'Stock', self)
        # self.show()

    def saveToDB(self):
        # print('isSaveToDB', self.isSaveToDB)
        if self.isSaveToDB: addStockPrice(self)