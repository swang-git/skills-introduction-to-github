from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy import Column, Integer, DateTime, CHAR, DECIMAL, and_, func
from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker
# import requests
# from bs4 import BeautifulSoup
from datetime import datetime
from tabulate import tabulate
import platform
import sys

def dbsession (database):
    if platform.system() == 'Darwin': dbconf="mysql+pymysql://swang:VVKKll11##@localhost/" + database + "?charset=utf8mb4" ## on Mac
    elif platform.system() == 'Linux': dbconf="mysql://swang:VVKKll11##@localhost/" + database + "?charset=utf8mb4"

    engine = create_engine(dbconf, echo=False)
    # engine = create_engine('mysql+pymysql://swang:VVKKll11##@localhost:3306/devx')
    Session = sessionmaker(bind=engine)
    session = Session()
    return session

class Portfolio:
    def __getattr__(self, key): return None
    def __init__(self, sec, quantity):
        self.load_time = sec.load_time
        self.symbol = sec.symbol
        self.price = sec.price
        self.price_change = sec.price_change
        self.shares = quantity
        self.values = quantity * sec.price
        self.day_gl = quantity * sec.price_change
        self.day_low = sec.day_low
        self.day_high = sec.day_high
        self.low_52_week = sec.low_52_week
        self.high_52_week = sec.high_52_week
        self.intradayChange = '' # A for after market
        self.intradayPrice = ''  # A for after market

Base = declarative_base()
class StockQuote(Base):
    __tablename__ = 'stock_quotes'
    id = Column(Integer, primary_key=True)
    load_time = Column(DateTime)
    symbol = Column(CHAR(8))
    price = Column(DECIMAL(12.3))
    price_change = Column(DECIMAL(12.3))
    low_52_week = Column(DECIMAL(12.3))
    high_52_week = Column(DECIMAL(12.4))
    day_low = Column(DECIMAL(12.4))
    day_high = Column(DECIMAL(12.4))
    status = Column(CHAR(1))
    def __init__(self, symbol):
        self.symbol = symbol
    def to_dict(self):
        return {
            'load_time': self.load_time,
            'symbol': self.symbol,
            'price': self.price,
            'price_change': self.price_change,
            'day_low': self.day_low,
            'day_high': self.day_high,
            'low_52_week': self.low_52_week,
            'high_52_week': self.high_52_week,
        }
    
    def isQuoteAlreadyInDBforToday(self, database):
        session = dbsession(database)
        load_hour=datetime.now().strftime('%Y-%m-%d %H')
        # print('load_date=[%s][%s]'%(load_hour, self.symbol))
        u = session.query(StockQuote).filter(
            StockQuote.symbol==self.symbol,
            func.date_format(StockQuote.load_time, '%Y-%m-%d %H').label('formated_date')==load_hour).first()

        if u is None: ## no data in DB
            return False
        else:
            print(f'quote for [{self.symbol}] already exists[THIS HOUR do it next hour] in table {database}.stock_quotes, see below:')
            print(tabulate(
                [(u.load_time, u.symbol, u.price, u.price_change, u.day_low,u.day_high,u.low_52_week,u.high_52_week)],
                headers=['Load Time', 'Stock', 'Price', 'Change', 'Day Low', 'Day High', '52WK Low', '52WK High'],
                tablefmt='grid'))

            return True

    def getQuote(self, database, flag=None):
        if self.isQuoteAlreadyInDBforToday(database): return 'exist this hour'
        # else: print('Loading data via scraping...')

        if flag == 'testing':
            self.load_time = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
            self.price=111.11
            self.price_change=-1.1
            self.day_low=222.22
            self.day_high=222.33
            self.low_52_week=333.33
            self.high_52_week=333.44
            self.status='A'
            return
        
        url = "https://finance.yahoo.com/quote/" + self.symbol
        # url = "https://www.google.com/search?q=" + self.symbol + "+stock+price+today"
        # url = "https://www.google.com/search?q=" + self.symbol
        # url = "https://www.bing.com/search?q=msft"
        # print(f"url={url}")
        # print(url)
        headers = {'User-Agent': 'Mozilla/5.0'}
        # headers = {
#     "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8", 
#     "Accept-Encoding": "gzip, deflate, br", 
#     "Accept-Language": "en-US,en;q=0.9", 
#     "Host": "httpbin.org", 
#     "Priority": "u=0, i", 
#     "Sec-Fetch-Dest": "document", 
#     "Sec-Fetch-Mode": "navigate", 
#     "Sec-Fetch-Site": "none", 
    # "User-Agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Safari/605.1.15", 
    # "X-Amzn-Trace-Id": "Root=1-68600c84-6744e5541895445a4982fb77"
#   }
        response = requests.get(url, headers=headers)
        soup = BeautifulSoup(response.text, 'html.parser')
        # print(soup.prettify())
        # streamers=soup.find_all('fin-streamer', {'data-symbol': self.symbol})
        # # streamers=soup.find_all('fin-streamer', {'data-field':'regularMarketChange'})
        # # x = streamers.split('</fin-streamer>')
        # for x in streamers: print(x)
        # sys.exit(0)
        curr_price=soup.find('span', {'data-testid':"qsp-post-price"}).text
        # curr_price=soup.find('span', class_='yf-ipw1h0.base')
        # curr_price=soup.find('span', {'data-testid':"qsp-post-price"}, class_='yf-ipw1h0.base')
        # curr_price=soup.find('span', {'jsname':"vWLAgc"}, class_='IsqQVc NprOob wT3VGc')
        # curr_price=soup.find('span', {'jsname':"vWLAgc"})
        # curr_price=soup.find('span', class_='l_ecrd_vqfcts_lnk')
        # curr_price_span=soup.find('span')
        # curr_price=curr_price_span.text
        # print(curr_price_span.prettify())
        # print(curr_price)
        # sys.exit(0)
        price_change=soup.find('span', {'data-testid':"qsp-price-change"}).text
        # print(price_change)
        price_change_percent=soup.find('span', {'data-testid':"qsp-price-change-percent"}).text
        # print(price_change_percent)
        open_price=soup.find('fin-streamer', {'data-symbol': self.symbol, 'data-field':'regularMarketOpen'}).text
        # curr_price=soup.find('fin-streamer', {'data-symbol': self.symbol, 'data-field':'regularMarketPrice'}).text
        # print('curr_price = [%s] open_price[%s]'%(curr_price,open_price))
        # print('open_price = [%s]'%open_price)
        # prchange=soup.find('fin-streamer', {'data-symbol': self.symbol, 'data-field':'regularMarketChange'}).text
        # price_change=soup.find('fin-streamer', {'data-field':'regularMarketChange'}).text
        price=curr_price
        price_change=price_change
        # print('price_change = %s'%price_change)
        prchangepct=soup.find('fin-streamer', {'data-field':'regularMarketChangePercent'}).text
        # print('prchangepct = %s'%prchangepct)
        dayRange=soup.find('fin-streamer', {'data-symbol': self.symbol, 'data-field':'regularMarketDayRange'}).text
        day_low, day_high = dayRange.split(' - ')
        # print('dayRange = %s'%dayRange)
        range52WK=soup.find('fin-streamer', {'data-symbol': self.symbol, 'data-field':'fiftyTwoWeekRange'}).text
        low_52_week, high_52_week = range52WK.split(' - ')
        # print('range52WK = %s'%range52WK)
        self.load_time = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        # self.price=open_price
        self.price=curr_price
        self.price_change=price_change
        self.day_low=day_low
        self.day_high=day_high
        self.low_52_week=low_52_week
        self.high_52_week=high_52_week
        self.status='A'

    def showData(self):
        print(f"{' ':>24} price = {self.price:>7} change = {self.price_change:>6} [{self.symbol:>4}]")

    def gotData(self):
        return self.price != None

    def getFakeQuote(self): # for testing
        self.load_time = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        self.price=111.11
        self.price_change=-1.1
        self.day_low=222.22
        self.day_high=222.33
        self.low_52_week=333.33
        self.high_52_week=333.44
        self.status='A'
    
    def saveToDB(self, database):
        # print(f'\nSAVING STOCK QUOTES TO {database}.stock_quotes')
        session = dbsession(database)
        session.add(self)
        session.commit()
        session.close()

    def setData(self, pdata):
        # if self.isQuoteAlreadyInDBforToday(database): return 'exist this hour'
        # print(pdata)
        self.load_time = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        self.price=pdata[1]
        self.price_change=-pdata[2]
        self.day_low=pdata[3]
        self.day_high=pdata[4]
        self.low_52_week=pdata[5]
        self.high_52_week=pdata[6]
        self.status='A'
        # print(self.to_dict())