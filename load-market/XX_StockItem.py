from bs4 import BeautifulSoup, Comment
import sys, time
from Utils import dlout
from Utils import padsp
from Utils import pedsp
from datetime import datetime
from urllib.request import Request, urlopen
from DB import getLastPrice, addStockPrice
import requests_html
import lxml.html as lh

# from sty import fg, bg, ef, rs, RgbFg
from Utils import boldit, isfloat, colorShow

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

    # def XXXgetPrice(self, spanId):
    #     stxt = 'span[data-reactid="{}"]'.format(spanId)
    #     try:
    #         pa = self.soup.select(stxt)
    #         # self.dxout('price', pa)
    #         if not pa: return # pa empty default to 0.0 as initialized
    #         ptxt = pa[0].get_text()
    #         if ptxt == 'Sustainability': return ptxt
    #         prc = pa[0].get_text()
    #         if  isfloat(prc):
    #             self.price = prc # after market price
    #             self.intradayPrice = 'a' # after market price
    #             return prc
    #     except Exception as e:
    #         print('get %s error:[%s]\nexiting...'%(type, e))
    #         self.dxout(type, pa)
    #         sys.exit(0)

    # def XXXsetPrice(self):
    #     try:
    #         self.price = str(self.soup).split('data-reactid="32"')[4].split('</span>')[0].replace('>','')
    #     except IndexError as e:
    #         self.price = 0.00
    #     self.price = self.price or "0"
    #     try:
    #         self.price = float(self.price.replace(',',''))
    #     except ValueError as e:
    #         self.price = 0.00
    #     print('price', self.price)
    def setPrice(self):
        try:
            # pa = self.soup.select('span[data-reactid="55"]') # after market price
            pi = self.soup.select('span[data-reactid="32"]') # intraday price
            # pi = self.soup.select('span[data-reactid="50"]') # intraday price
            # if not pa and not pi: return # both are empty default to 0.0 as initialized
            # prc = pa[0].get_text()
            # if  isfloat(prc) == True:
            #     self.price = prc # after market price
            #     self.intradayPrice = 'a' # after market price
            #     return
            prc = pi[0].get_text()
            if isfloat(prc) == True:
                self.price = prc # intraday price
                # print('price', self.price)
                return
        except Exception as e:
            print('get PricE for [%s] error:[%s] %s\nexiting...'%(self.symbol, e, pi))
            sys.exit(0)
    # def XXXsetChange(self):
    #     try:
    #         pa = str(self.soup).split('data-reactid="33"')[4].split('</span>')[0].replace('>','')
    #         # pa = str(self.soup).split('data-reactid="33"')
    #     except IndexError as e:
    #         pa = 0.00
    #     self.change = pa or "0"
    #     # try:
    #     #     self.pa = float(pa.replace(',',''))
    #     # except ValueError as e:
    #     #     self.change = 0.00
    #     print('change', self.change)
    def setChange(self):
        try:
            # pa = self.soup.select('span[data-reactid="58"]') # after market
            pi = self.soup.select('span[data-reactid="33"]') # intraday
            # self.dout('Check change pa/pi', self.symbol, pa, pi)
            # if pa and len(pa) == 2: 
            # if pa and len(pa) >= 1 and 'comment-btn-count' not in pa[0].get('class'):
            #     pch = pa[0].get_text().split(' ')[0]
            #     if isfloat(pch):
            #         self.intradayChange = 'a'
            #         self.change = pch
            #         return
            pch = pi[1].get_text().split(' ')[0]   # pm not empty
            if isfloat(pch):
                self.change = pch
                # print('change', self.change)
                return
        except Exception as e:
            print('get ChangE error:[%s] %s \nexiting...'%(e, pi))
            sys.exit(0)

    # def XXXset52weekHiLow(self):
    #     pw = self.soup.select('tbody[data-reactid="67"]')[0].find_all('td')
    #     # print('--- debug>>>> price:%s, change:%s'%(self.price, self.change))
    #     if len(pw) <= 0: return # default to 0.0 as initialized
    #     for da in enumerate(pw):
    #         # if int(da[1]['data-reactid']) == 121 and 'FIFTY_TWO_WK_RANGE' in da[1]['data-test']:
    #         if da[1].get('data-test') is None: continue
    #         elif "FIFTY_TWO_WK_RANGE" in da[1].get('data-test'):
    #             hilowx = da[1].get_text().split(' - ')
    #             self.lo52w = hilowx[0]
    #             self.hi52w = hilowx[1]
    #             break
    #     print('Low, Hi', self.lo52w, self.hi52w)
    def DEBUG_chk_tds(self):
        tds = self.soup.find_all('td')
        for td in tds: print(td)
        sys.exit(0)

    def set52weekHiLow(self):
        # self.DEBUG_chk_tds()
        reactid = 67
        pwx = self.soup.select(f'td[data-reactid="{reactid}"]')
        # print('pwx', pwx, reactid)
        if len(pwx) != 1:
            reactid = 66
            pwx = self.soup.select(f'td[data-reactid="{reactid}"]')
        pw = pwx[0]
        hilowx = pw.get_text().split(' - ')
        if len(hilowx) != 2:
            reactid = 65
            pw = self.soup.select(f'td[data-reactid="{reactid}"]')[0]
            hilowx = pw.get_text().split(' - ')
        self.lo52w = hilowx[0]
        self.hi52w = hilowx[1]
        # print('--- debug>>>> price:%s, change:%s'%(self.price, self.change))
        if len(pw) <= 0: return # default to 0.0 as initialized
        # for da in enumerate(pw):
        #     # if int(da[1]['data-reactid']) == 121 and 'FIFTY_TWO_WK_RANGE' in da[1]['data-test']:
        #     if da[1].get('data-test') is None: continue
        #     elif "FIFTY_TWO_WK_RANGE" in da[1].get('data-test'):
        #         hilowx = da[1].get_text().split(' - ')
        #         self.lo52w = hilowx[0]
        #         self.hi52w = hilowx[1]
        #         break
        # print('Low, Hi', self.lo52w, self.hi52w)
    def getQuote(self):
        self.lastupd = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        # self.lnk = 'https://finance.yahoo.com/quote/' + self.symbol + '?q=' + self.symbol
        self.lnk = 'https://in.finance.yahoo.com/quote/' + self.symbol + '?p=' + self.symbol
        session = requests_html.HTMLSession()
        r = session.get(self.lnk)
        self.soup = BeautifulSoup(r.content, 'lxml')
        # print('open URL', self.lnk)
        # headers = {'User-Agent': "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36"}
        # try:
        #     # req = Request(self.lnk, None, headers = headers)
        #     # page = urlopen(req).read()

        #     self.soup = BeautifulSoup(page, "html.parser").find('body') # try "html5lib" # print(self.soup.prettify())
        # except Exception as e:
        #     print('WW getInfo Exception, return None and the error is:', e)
        #     return None
        
        # prc = self.getPrice(55) # after market price
        # if not isfloat(prc): self.getPrice(50) # intraday price
        self.setPrice()
        self.setChange()
        self.set52weekHiLow()
        # sys.exit(0)
        # print('--- debug>>>> price:%s, change:%s'%(self.price, self.change))

    def display(self, sp):
        # print('symbol:%s, price:%s, change:%s, lo52w:%s, hi52w:%s'%(self.symbol, self.price, self.change, self.lo52w, self.hi52w))
        lprice = getLastPrice(self.symbol)
        if lprice is None:
            print('Can not get last price for', self.symbol, 'exiting ...')
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