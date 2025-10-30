from bs4 import BeautifulSoup, Comment
import sys, time, requests_html
from urllib.request import Request, urlopen
from datetime import datetime, date
# sys.path.insert(0, '/sites/projects/load-market')
# # print(sys.path)
# from LM_Utils import dlout, boldit, padsp, isfloat, pedsp, colorShow
# from LM_DB import getLastPrice, addStockPrice, getTodaysHiLo
from LM_Utils import isfloat
from LM_DB import getTodaysHiLo

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
        # self.lnk = "https://in.finance.yahoo.com/quote/" + self.symbol
        self.lnk = "https://finance.yahoo.com/quote/" + self.symbol
        # self.lnk = 'file:///home/swang/tmp/' + self.symbol + '.html'
        session = requests_html.HTMLSession()
        try:
            r = session.get(self.lnk)
            self.soup = BeautifulSoup(r.content, 'lxml')

        except Exception as e:
            print('WW getInfo Exception, return None and the error is:', e)
            return None

        # prc = self.soup.select('span[data-reactid="32"]')
        # pch = self.soup.select('span[data-reactid="33"]')
        idmap = dict()
        for spn in self.soup.find_all('span'):
            if not isfloat(spn.get_text().split(' ')[0]): continue
            idmap[int(spn['data-reactid'])] = float(spn.get_text().split(' ')[0])
        # print(idmap)
        self.price = idmap[32]
        self.change = idmap[33]
        self.lastupd = datetime.now().strftime('%Y-%m-%d %H:%M:%S')

        fundHiLow = FundHiLow(self.symbol)
        [self.lo52w, self.hi52w] = fundHiLow.get52WeekHiLow()

    # def display(self, sp):
    #     lprice = getLastPrice(self.symbol)
    #     if lprice is None:
    #         print('Can not get last price for', sec.symbol, 'exiting ...')
    #         sys.exit(100)
    #     tag = 'x '
    #     change = float(self.price) - float(lprice)
    #     if change > 0: tag = '+'
    #     elif change == 0: tag = '='
    #     elif change < 0: tag = '-'
    #     self.tag = tag
    #     colorShow(sp, 'Funds', self)

    # def saveToDB(self):
        # if self.isSaveToDB: addStockPrice(self)

class FundHiLow:
    def __getattr__(self, key): return None
    def __init__(self, symbol):
        self.symbol = symbol
        self.lo52w = 0.0
        self.hi52w = 0.0

    def get52WeekHiLow(self):
        hl = getTodaysHiLo(self.symbol)
        if hl != None:
            # [self.lo52w, self.hi52w] = hl
            # print('FROM DB:', self.lo52w, self.hi52w)
            return hl

        # self.lnk = 'https://finance.yahoo.com/quote/FFTWX/history?p=FFTWX'
        # self.lnk = 'https://finance.yahoo.com/quote/FFTWX/history?period1=1554693541&period2=1586229445&interval=1mo&filter=history&frequency=1mo'
        now = int(time.time())
        oneYearInSeconds = 365 * 24 * 60 * 60
        w52 = now - oneYearInSeconds # one year ago
        self.lnk = 'https://finance.yahoo.com/quote/%s/history?period1=%s&period2=%s&interval=1wk&filter=history&frequency=1wk'%(self.symbol, w52, now)
        # self.lnk = 'file:///home/swang/projects/load-ymarket/lllyh.html'
        # print('open HiLow URL', self.lnk)
        headers = {'User-Agent': "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36"}
        try:
            req = Request(self.lnk, None, headers = headers)
            # print(req)
            # page = urlopen(req).read().decode('utf-8')
            page = urlopen(req).read()
            # with urlopen(req) as page:
            # print(page)
            # self.soup = BeautifulSoup(page,  "html.parser").find('body').find('tbody')
            self.soup = BeautifulSoup(page,  "html.parser").find('tbody')
            # print(self.soup.prettify())
        except Exception as e:
            print('WW getInfo Exception, return None and the error is:', e)
            return None

        tdat = self.soup.find_all('td')
        all = []
        for t in tdat:
            val = t.get_text()
            if isfloat(val):
                if float(val) < 1.0: print(val)
                all.append(float(val))
        self.lo52wk = min(all)
        self.hi52wk = max(all)
        # print(self.lo52wk, self.hi52wk)
        return [self.lo52wk, self.hi52wk]