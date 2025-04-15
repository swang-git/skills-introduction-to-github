from bs4 import BeautifulSoup, Comment
import re, time, sys
from urllib.request import Request, urlopen
from Utils import isfloat
from Models import StockPrice
from LM_DB import getTodaysHiLo

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