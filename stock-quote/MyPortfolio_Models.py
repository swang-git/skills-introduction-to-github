from sqlalchemy import create_engine, Column, VARCHAR, DECIMAL, DATE, DATETIME, Float, Integer
from sqlalchemy.orm import declarative_base, sessionmaker

# =============================================================================
# 1. YOUR MYSQL DATABASE SETTINGS (UPDATE THESE!)
# =============================================================================
# SessionLocal = None

def get_connection(database):
    MYSQL_USER = "swang"
    MYSQL_PASSWORD = "Ybsjll11"
    MYSQL_HOST = "localhost"
    MYSQL_DATABASE = database  # CREATE THIS IN MYSQL FIRST

    # MySQL Connection String
    DATABASE_URL = f"mysql+pymysql://{MYSQL_USER}:{MYSQL_PASSWORD}@{MYSQL_HOST}/{MYSQL_DATABASE}"
    engine = create_engine(DATABASE_URL, echo=False)  # echo=True shows SQL queries
    SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)
    return SessionLocal(), engine.raw_connection()


# =============================================================================
# 1. MYSQL TABLE MODEL: HealthRecord (YOUR EXACT SCHEMA)
# =============================================================================
Base = declarative_base()
class HealthRecord(Base):
    __tablename__ = "health_records"
    id = Column(Integer, primary_key=True, autoincrement=True)
    date = Column(DATE, nullable=False, index=True)
    DOW_JONES = Column(DECIMAL(12, 3), nullable=False)
    NASDAQ = Column(DECIMAL(12, 3), nullable=False)
    SP500 = Column(DECIMAL(12, 3), nullable=False)
    FTSE100 = Column(DECIMAL(12, 3), nullable=False)
    NIKKEI = Column(DECIMAL(12, 3), nullable=False)
    portfolio = Column(DECIMAL(12, 3), nullable=False)

# =============================================================================
# 2. MYSQL TABLE MODEL: MyPortfolio (YOUR EXACT SCHEMA)
# =============================================================================
class StockQuote(Base):
    __tablename__ = "stock_quotes"
    id = Column(Integer, primary_key=True, autoincrement=True)
    asof_time = Column(DATETIME, nullable=False, index=True)
    symbol = Column(VARCHAR(9), nullable=True)
    # 👇 这些都允许 NULL 了
    price = Column(DECIMAL(12, 3), nullable=True)
    price_change = Column(DECIMAL(12, 3), nullable=True)
    low_52_week = Column(DECIMAL(12, 3), nullable=True)
    high_52_week = Column(DECIMAL(12, 3), nullable=True)
    # def __getattr__(self, key): return None
    def __init__(self, asof, symb, prc, prc_chg, low_52wk, high_52wk):
        self.asof_time = asof
        self.symbol = symb
        self.price = prc
        self.price_change = prc_chg
        self.low_52_week = low_52wk
        self.high_52_week = high_52wk


class MyPortfolio(Base):
    __tablename__ = "my_portfolios"

    id = Column(Integer, primary_key=True, autoincrement=True)
    asof_time = Column(DATETIME, nullable=False, index=True)
    account = Column(VARCHAR(9), nullable=False, index=True)
    
    account_name = Column(VARCHAR(30), nullable=True)  # 允许空
    symbol = Column(VARCHAR(9), nullable=True)
    company = Column(VARCHAR(128), nullable=True)
    
    # 👇 这些都允许 NULL 了
    quantity = Column(DECIMAL(12, 3), nullable=True)
    price = Column(DECIMAL(12, 3), nullable=True)
    price_change = Column(DECIMAL(12, 3), nullable=True)
    current_value = Column(DECIMAL(12, 2), nullable=True)
    today_gl = Column(DECIMAL(12, 2), nullable=True)
    today_gl_pct = Column(DECIMAL(12, 2), nullable=True)
    total_gl = Column(DECIMAL(12, 2), nullable=True)
    total_gl_pct = Column(DECIMAL(12, 2), nullable=True)
    pct_of_account = Column(Float, nullable=True)
    total_cost = Column(DECIMAL(12, 3), nullable=True)
    cost_per_share = Column(DECIMAL(12, 3), nullable=True)
    type = Column(VARCHAR(4), nullable=True)
    low_52_week = Column(DECIMAL(12, 3), nullable=True)
    high_52_week = Column(DECIMAL(12, 3), nullable=True)

# Auto-create table in MySQL (if not exists)
# Base.metadata.create_all(bind=engine)

# =============================================================================
# 3. YOUR EXACT CSV → DB COLUMN MAPPING
# =============================================================================
CSV_TO_DB_MAP = {
    "account number": "account",
    "account name": "account_name",
    "symbol": "symbol",
    "description": "company",
    "quantity": "quantity",
    "last price": "price",
    "last price change": "price_change",
    "current value": "current_value",
    "today's gain/loss dollar": "today_gl",
    "today's gain/loss percent": "today_gl_pct",
    "total gain/loss dollar": "total_gl",
    "total gain/loss percent": "total_gl_pct",
    "percent of account": "pct_of_account",
    "cost basis total": "total_cost",
    "average cost basis": "cost_per_share",
    "type": "type"
}
# CSV_TO_DB_MAP = {
#     "Account number": "account",
#     "Account name": "account_name",
#     "Symbol": "symbol",
#     "Description": "company",
#     "Quantity": "quantity",
#     "Last price": "price",
#     "Last price Change": "price_change",
#     "Current value": "current_value",
#     "Today's gain/loss dollar": "today_gl",
#     "Today's gain/loss percent": "today_gl_pct",
#     "Total gain/loss dollar": "total_gl",
#     "Total gain/loss percent": "total_gl_pct",
#     "Percent of account": "pct_of_account",
#     "Cost basis total": "total_cost",
#     "Average cost basis": "cost_per_share",
#     "Type": "type"
# }

# =============================================================================
# 4. DATA TYPE CONVERSION (DECIMAL, FLOAT, ETC.)
# =============================================================================
def clean_decimal(val):
    """Remove $, commas, % → convert to float for DECIMAL columns"""
    if not val:
        return None
    val = str(val).strip().replace('$', '').replace(',', '').replace('%', '') ##.strip("*")
    try:
        return float(val)
    except:
        return None

TYPE_CONVERTERS = {
    # Decimal(12,3)
    "quantity": clean_decimal,
    "price": clean_decimal,
    "price_change": clean_decimal,
    "total_cost": clean_decimal,
    "cost_per_share": clean_decimal,

    # Decimal(12,2)
    "current_value": clean_decimal,
    "today_gl": clean_decimal,
    "today_gl_pct": clean_decimal,
    "total_gl": clean_decimal,
    "total_gl_pct": clean_decimal,

    # Float
    "pct_of_account": clean_decimal,
    # "symbol": clean_decimal
}


# from sqlalchemy.ext.declarative import declarative_base
# from sqlalchemy import Column, Integer, DateTime, CHAR, DECIMAL, String, and_, func
# from sqlalchemy import create_engine
# from sqlalchemy.orm import sessionmaker
# import requests
# from bs4 import BeautifulSoup
# from datetime import datetime
# from tabulate import tabulate
# import platform
# import sys

# def dbsession (database):
#     if platform.system() == 'Darwin': dbconf="mysql+pymysql://swang:Ybsjll11@localhost/" + database + "?charset=utf8mb4" ## on Mac
#     elif platform.system() == 'Linux': dbconf="mysql://swang:Ybsjll11@localhost/" + database + "?charset=utf8mb4"

#     engine = create_engine(dbconf, echo=False)
#     # engine = create_engine('mysql+pymysql://swang:Ybsjll11@localhost:3306/devx')
#     Session = sessionmaker(bind=engine)
#     session = Session()
#     return session

# class Portfolio:
#     def __getattr__(self, key): return None
#     def __init__(self, sec, quantity):
#         self.load_time = sec.load_time
#         self.symbol = sec.symbol
#         self.price = sec.price
#         self.price_change = sec.price_change
#         self.shares = quantity
#         self.values = quantity * sec.price
#         self.day_gl = quantity * sec.price_change
#         self.day_low = sec.day_low
#         self.day_high = sec.day_high
#         self.low_52_week = sec.low_52_week
#         self.high_52_week = sec.high_52_week
#         self.intradayChange = '' # A for after market
#         self.intradayPrice = ''  # A for after market

# Base = declarative_base()
# class MyPortfolio(Base):
#     __tablename__ = 'my_portfolios'
#     id = Column(Integer, primary_key=True)
#     asof_time = Column(DateTime)
#     account = Column(CHAR(9))
#     account_name = Column(CHAR(16))
#     company = Column(CHAR(32))
#     symbol = Column(CHAR(8))
#     price = Column(DECIMAL(12.3))
#     price_change = Column(DECIMAL(12.3))
#     today_gl = Column(DECIMAL(12.3))
#     today_gl_pct = Column(DECIMAL(12.3))
#     total_gl = Column(DECIMAL(12.3))
#     total_gl_pct = Column(DECIMAL(12.3))
#     current_value = Column(DECIMAL(12.3))
#     pct_of_account = Column(DECIMAL(12.3))
#     quantity = Column(DECIMAL(12.3))
#     total_cost = Column(DECIMAL(12.3))
#     cost_per_share = Column(DECIMAL(12.3))
#     low_52_week = Column(DECIMAL(12.3))
#     high_52_week = Column(DECIMAL(12.4))
#     status = Column(CHAR(1))

#     def __getitem__(self, index):
#         columns = list(self.__table__.columns)
#         if 0 <= index < len(columns):
#             return getattr(self, columns[index].name)
#         raise IndexError("Column index out of range")
    
#     # def __getattr__(self, key): return None
#     # def __init__(self, row):
#     # # Copy ALL fields from the 'row' object to your MyPortfolio object
#     #     super().__init__()
#     #     self.id = row.id
#     #     self.asof_time = row.asof_time
#     #     self.account = row.account
#     #     self.account_name = row.account_name
#     #     self.company = row.company
#     #     self.symbol = row.symbol
#     #     self.price = row.price
#     #     self.price_change = row.price_change
#     #     self.today_gl = row.today_gl
#     #     self.today_gl_pct = row.today_gl_pct
#     #     self.current_value = row.current_value
#     #     self.pct_of_account = row.pct_of_account
#     #     self.quantity = row.quantity
#     #     self.total_cost = row.total_cost
#     #     self.cost_per_share = row.cost_per_share
#     #     self.low_52_week = row.low_52_week
#     #     self.high_52_week = row.high_52_week
#     #     self.status = row.status
#     #     self.created_at = row.created_at
#     #     self.updated_at = row.updated_at

#     # def __init__(self, sec):
#     #     self.id = sec.id
#     #     self.asof_time = sec.asof_time
#     #     self.account = sec.account
#     #     self.account_name = sec.account_name
#     #     self.company = sec.company
#     #     self.symbol = sec.symbol
#     #     self.price = sec.price
#     #     self.price_change = sec.price_change
#     #     self.today_gl = sec.today_gl
#     #     self.today_gl_pct = sec.today_gl_pct
#     #     self.current_value = sec.current_value
#     #     self.pct_of_account = sec.pct_of_account
#     #     self.quantity = sec.quantity
#     #     self.total_cost = sec.total_cost
#     #     self.cost_per_share = sec.cost_per_share
#     #     self.low_52_week = sec.low_52_week
#     #     self.high_52_week = sec.high_52_week
#     #     self.status = sec.status
#     #     self.created_at = sec.created_at
#     #     self.updated_at = sec.updated_at

#     def to_dict(self):
#         return {
#             'asof_time': self.asof_time.strftime("%Y-%m-%d %H:%M"),
#             'account': self.account,
#             'account_name': self.account_name,
#             'company': self.company,
#             'symbol': self.symbol,
#             'price': float(self.price),
#             'price_change': float(self.price_change),
#             'today_gl': float(self.today_gl),
#             'today_gl_pct': float(self.today_gl_pct),
#             'current_value': float(self.current_value),
#             'pct_of_account': float(self.pct_of_account),
#             'quantity': float(self.quantity),
#             'total_cost': float(self.total_cost),
#             'cost_per_share': float(self.cost_per_share),
#             'low_52_week': float(self.low_52_week),
#             'high_52_week': float(self.high_52_week),
#         }
#     def print_pretty(self):
#         """Print all columns on a separate line with clean formatting"""
#         print("=" * 60)
#         print(f"ID:                {self.id}")
#         print(f"Asof Time:         {self.asof_time.strftime('%Y-%m-%d %H:%M') if self.asof_time else None}")
#         print(f"Account:           {self.account}")
#         print(f"Account Name:      {self.account_name}")
#         print(f"Company:           {self.company}")
#         print(f"Symbol:            {self.symbol}")
#         print(f"Price:             {float(self.price)}")
#         print(f"Price Change:      {float(self.price_change)}")
#         print(f"Today GL:          {float(self.today_gl)}")
#         print(f"Today GL %:        {float(self.today_gl_pct)}")
#         print(f"Current Value:     {float(self.current_value)}")
#         print(f"% of Account:      {float(self.pct_of_account)}")
#         print(f"Quantity:          {float(self.quantity)}")
#         print(f"Total Cost:        {float(self.total_cost)}")
#         print(f"Cost Per Share:    {'--' if self.cost_per_share == None else float(self.cost_per_share)}")
#         print(f"52-Week Low:       {'--' if self.low_52_week == None else float(self.low_52_week)}")
#         print(f"52-Week High:      {'--' if self.high_52_week == None else float(self.high_52_week)}")
#         print(f"Status:            {self.status}")
#         print("=" * 60)

# class StockQuote(Base):
#     __tablename__ = 'stock_quotes'
#     id = Column(Integer, primary_key=True)
#     load_time = Column(DateTime)
#     symbol = Column(CHAR(8))
#     price = Column(DECIMAL(12.3))
#     price_change = Column(DECIMAL(12.3))
#     low_52_week = Column(DECIMAL(12.3))
#     high_52_week = Column(DECIMAL(12.4))
#     day_low = Column(DECIMAL(12.4))
#     day_high = Column(DECIMAL(12.4))
#     status = Column(CHAR(1))
#     def __init__(self, symbol):
#         self.symbol = symbol
#     def to_dict(self):
#         return {
#             'load_time': self.load_time,
#             'symbol': self.symbol,
#             'price': self.price,
#             'price_change': self.price_change,
#             'day_low': self.day_low,
#             'day_high': self.day_high,
#             'low_52_week': self.low_52_week,
#             'high_52_week': self.high_52_week,
#         }
    
#     def isQuoteAlreadyInDBforToday(self, database):
#         session = dbsession(database)
#         load_hour=datetime.now().strftime('%Y-%m-%d %H')
#         # print('load_date=[%s][%s]'%(load_hour, self.symbol))
#         u = session.query(StockQuote).filter(
#             StockQuote.symbol==self.symbol,
#             func.date_format(StockQuote.load_time, '%Y-%m-%d %H').label('formated_date')==load_hour
#         ).first()

#         if u is None: ## no data in DB
#             return False
#         else:
#             print(f'quote for [{self.symbol}] already exists[THIS HOUR do it next hour] in table {database}.stock_quotes, see below:')
#             print(tabulate(
#                 [(u.load_time, u.symbol, u.price, u.price_change, u.day_low,u.day_high,u.low_52_week,u.high_52_week)],
#                 headers=['Load Time', 'Stock', 'Price', 'Change', 'Day Low', 'Day High', '52WK Low', '52WK High'],
#                 tablefmt='grid'))

#             return True

#     def getQuote(self, database, flag=None):
#         if self.isQuoteAlreadyInDBforToday(database): return 'exist this hour'
#         # else: print('Loading data via scraping...')

#         if flag == 'testing':
#             self.load_time = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
#             self.price=111.11
#             self.price_change=-1.1
#             self.day_low=222.22
#             self.day_high=222.33
#             self.low_52_week=333.33
#             self.high_52_week=333.44
#             self.status='A'
#             return
        
#         url = "https://finance.yahoo.com/quote/" + self.symbol
#         # print(f"url={url}")
#         # print(url)
#         headers = {'User-Agent': 'Mozilla/5.0'}
#         response = requests.get(url, headers=headers)
#         soup = BeautifulSoup(response.text, 'html.parser')
#         # print(soup.prettify())
#         # streamers=soup.find_all('fin-streamer', {'data-symbol': self.symbol})
#         # # streamers=soup.find_all('fin-streamer', {'data-field':'regularMarketChange'})
#         # # x = streamers.split('</fin-streamer>')
#         # for x in streamers: print(x)
#         # sys.exit(0)
#         curr_price=soup.find('span', {'data-testid':"qsp-price"}).text
#         # print(curr_price)
#         price_change=soup.find('span', {'data-testid':"qsp-price-change"}).text
#         # print(price_change)
#         price_change_percent=soup.find('span', {'data-testid':"qsp-price-change-percent"}).text
#         # print(price_change_percent)
#         open_price=soup.find('fin-streamer', {'data-symbol': self.symbol, 'data-field':'regularMarketOpen'}).text
#         # curr_price=soup.find('fin-streamer', {'data-symbol': self.symbol, 'data-field':'regularMarketPrice'}).text
#         # print('curr_price = [%s] open_price[%s]'%(curr_price,open_price))
#         # print('open_price = [%s]'%open_price)
#         # prchange=soup.find('fin-streamer', {'data-symbol': self.symbol, 'data-field':'regularMarketChange'}).text
#         # price_change=soup.find('fin-streamer', {'data-field':'regularMarketChange'}).text
#         price=curr_price
#         price_change=price_change
#         # print('price_change = %s'%price_change)
#         prchangepct=soup.find('fin-streamer', {'data-field':'regularMarketChangePercent'}).text
#         # print('prchangepct = %s'%prchangepct)
#         dayRange=soup.find('fin-streamer', {'data-symbol': self.symbol, 'data-field':'regularMarketDayRange'}).text
#         day_low, day_high = dayRange.split(' - ')
#         # print('dayRange = %s'%dayRange)
#         range52WK=soup.find('fin-streamer', {'data-symbol': self.symbol, 'data-field':'fiftyTwoWeekRange'}).text
#         low_52_week, high_52_week = range52WK.split(' - ')
#         # print('range52WK = %s'%range52WK)
#         self.load_time = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
#         # self.price=open_price
#         self.price=curr_price
#         self.price_change=price_change
#         self.day_low=day_low
#         self.day_high=day_high
#         self.low_52_week=low_52_week
#         self.high_52_week=high_52_week
#         self.status='A'

#     def showData(self):
#         print(f"{' ':>24} price = {self.price:>7} change = {self.price_change:>6} [{self.symbol:>4}]")

#     def gotData(self):
#         return self.price != None

#     def getFakeQuote(self): # for testing
#         self.load_time = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
#         self.price=111.11
#         self.price_change=-1.1
#         self.day_low=222.22
#         self.day_high=222.33
#         self.low_52_week=333.33
#         self.high_52_week=333.44
#         self.status='A'
    
#     def saveToDB(self, database):
#         # print(f'\nSAVING STOCK QUOTES TO {database}.stock_quotes')
#         session = dbsession(database)
#         session.add(self)
#         session.commit()
#         session.close()
