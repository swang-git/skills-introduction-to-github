from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy import Column, Integer, DateTime, CHAR, DECIMAL, and_, func
# from sqlalchemy.dialects.mysql import VARCHAR
from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker
import requests
from bs4 import BeautifulSoup, Comment
import sys, time
from datetime import datetime
import json
from tabulate import tabulate

dbconf="mysql+pymysql://swang:VVKKll11##@localhost/devx?charset=utf8mb4"
# dbconf="mysql://swang:VVKKll11##@localhost/devx?charset=utf8mb4"
# dbconf="mysql://swang:VVKKll11##@localhost/prod?charset=utf8mb4"
# engine = create_engine(dbconf, encoding='utf8', echo=False)

Base = declarative_base()
engine = create_engine(dbconf, echo=False)
Session = sessionmaker(bind=engine)
session = Session()

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
    
    def isQuoteAlreadyInDBforToday(self):
        load_hour=datetime.now().strftime('%Y-%m-%d %H')
        print('load_date=[%s][%s]'%(load_hour, self.symbol))
        u = session.query(StockQuote).filter(
            StockQuote.symbol==self.symbol,
            func.date_format(StockQuote.load_time, '%Y-%m-%d %H').label('formated_date')==load_hour
        ).first()

        if u is None: ## no data in DB
            return False
        else:
            print('quote for %s already exists in table stock_quotes, exiting ...'%self.symbol)
            # jx=[{'symbo':result.symbol,'load_time':result.load_time,
            #     'price':result.price, 'price_change':result.price_change,
            #     'day_low':result.day_low,'day_high':result.day_high,
            #     'low_52_wk':result.low_52_week,'high_52_wk':result.high_52_week}]
            # print(json.dumps(jx, indent=2))
            # print('DB data:', f"{result.symbol}:{result.load_time},{result.price},{result.price_change},{result.day_low},{result.day_high},{result.low_52_week},{result.high_52_week}")

            print(tabulate(
                [(u.symbol, u.price, u.price_change, u.day_low,u.day_high,u.low_52_week,u.high_52_week)],
                headers=['Stock', 'Price', 'Change', 'Day Low', 'Day High', '52WK Low', '52WK High'],
                tablefmt='grid'))

            return True

    def getQuote(self, flag=None):
        if self.isQuoteAlreadyInDBforToday(): return
        else: print('Loading data via scraping...')

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
        print(url)
        headers = {'User-Agent': 'Mozilla/5.0'}
        response = requests.get(url, headers=headers)
        soup = BeautifulSoup(response.text, 'html.parser')
        # print(soup.prettify())
        # streamers=soup.find_all('fin-streamer', {'data-symbol': self.symbol})
        # # streamers=soup.find_all('fin-streamer', {'data-field':'regularMarketChange'})
        # # x = streamers.split('</fin-streamer>')
        # for x in streamers: print(x)
        # # sys.exit(0)
        curr_price=soup.find('span', {'data-testid':"qsp-price"}).text
        print(curr_price)
        price_change=soup.find('span', {'data-testid':"qsp-price-change"}).text
        print(price_change)
        price_change_percent=soup.find('span', {'data-testid':"qsp-price-change-percent"}).text
        print(price_change_percent)
        open_price=soup.find('fin-streamer', {'data-symbol': self.symbol, 'data-field':'regularMarketOpen'}).text
        # curr_price=soup.find('fin-streamer', {'data-symbol': self.symbol, 'data-field':'regularMarketPrice'}).text
        # print('curr_price = [%s] open_price[%s]'%(curr_price,open_price))
        print('open_price = [%s]'%open_price)
        # prchange=soup.find('fin-streamer', {'data-symbol': self.symbol, 'data-field':'regularMarketChange'}).text
        # price_change=soup.find('fin-streamer', {'data-field':'regularMarketChange'}).text
        price=curr_price
        price_change=price_change
        print('price_change = %s'%price_change)
        prchangepct=soup.find('fin-streamer', {'data-field':'regularMarketChangePercent'}).text
        print('prchangepct = %s'%prchangepct)
        dayRange=soup.find('fin-streamer', {'data-symbol': self.symbol, 'data-field':'regularMarketDayRange'}).text
        day_low, day_high = dayRange.split(' - ')
        print('dayRange = %s'%dayRange)
        range52WK=soup.find('fin-streamer', {'data-symbol': self.symbol, 'data-field':'fiftyTwoWeekRange'}).text
        low_52_week, high_52_week = range52WK.split(' - ')
        print('range52WK = %s'%range52WK)

        self.load_time = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        # self.price=open_price
        self.price=curr_price
        self.price_change=price_change
        self.day_low=day_low
        self.day_high=day_high
        self.low_52_week=low_52_week
        self.high_52_week=high_52_week
        self.status='A'

        self.saveToDB()
        # print(json.dumps(self.to_dict(), indent=2))

    def getFakeQuote(self): # for testing
        # url = "https://finance.yahoo.com/quote/" + self.symbol
        # print(url)
        self.load_time = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        self.price=111.11
        self.price_change=-1.1
        self.day_low=222.22
        self.day_high=222.33
        self.low_52_week=333.33
        self.high_52_week=333.44
        self.status='A'
    
    def saveToDB(self):
        # dx = session.query(StockQuote).filter_by(symbol=self.symbol).filter(StockQuote.load_time.like('%'+self.load_time[:10]+'%')).all()
        # dx = session.query(StockQuote).filter(
        #     StockQuote.symbol==self.symbol,
        #     # StockQuote.load_time.like('%'+self.load_time[:10]+'%')
        #     # StockQuote.symbol=='DELL',
        #     func.date_format(StockQuote.load_time, '%Y-%m-%d').label('formated_date')==self.load_time[:10]
        # ).all()
        
        # if self.isQuoteAlreadyInDBforToday():
        #     print('quote for %s already exists in table stock_quotes, exiting ...'%self.symbol)
        # else:
        #     session.add(self)
        #     session.commit()
        #     session.close()
        #     return
        # session.close()
        print('SAVING quote[%s] to table stock_quotes'%self.symbol)
        session.add(self)
        session.commit()
        session.close()
