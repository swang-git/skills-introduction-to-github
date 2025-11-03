from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy import Column, Integer, SmallInteger, DateTime, CHAR, String, FLOAT, DECIMAL
from sqlalchemy.dialects.mysql import VARCHAR, TEXT

Base = declarative_base()

class StockQuote(Base):
    __tablename__ = 'stock_quotes'
    id = Column(Integer, primary_key=True)
    load_time = Column(DateTime)
    symbol = Column(CHAR(8))
    price = Column(DECIMAL(12.3))
    price_change = Column(DECIMAL(12.3))
    day_low = Column(DECIMAL(12.3))
    day_high = Column(DECIMAL(12.3))
    low_52_week = Column(DECIMAL(12.3))
    high_52_week = Column(DECIMAL(12.3))
    # status = Column(CHAR(1))
    def __init__(self, sec):
        self.load_time = format(sec.lastupd)
        self.symbol = sec.symbol
        self.price = sec.price
        self.price_change = sec.price_change
        self.day_low = sec.day_low
        self.day_high = sec.day_high
        self.low_52_week = sec.low_52_week
        self.high_52_week = sec.high_52_week

class Portfolio(Base):
    __tablename__ = 'portfolios'
    id = Column(Integer, primary_key=True)
    asof_time = Column(DateTime)
    symbol = Column(CHAR(8))
    quantity = Column(DECIMAL(12.4))
    price = Column(DECIMAL(12.3))
    pchange = Column(DECIMAL(12.3))
    plow = Column(DECIMAL(12.3))
    phigh = Column(DECIMAL(12.4))
    pchange = Column(DECIMAL(12.4))
    status = Column(CHAR(1))
    def __init__(self, sec):
        self.asof_time = format(sec.lastupd)
        self.symbol = sec.symbol
        self.quantity = sec.quantity
        self.price = sec.price
        self.pchange = sec.change
        self.plow = sec.lo52w
        self.phigh = sec.hi52w
        self.status = 'A'

class StockPrice(Base):
    __tablename__ = 'stock_prices'
    id = Column(Integer, primary_key=True)
    sdatetime = Column(DateTime)
    symbol = Column(CHAR(8))
    price = Column(DECIMAL(12.3))
    pchange = Column(DECIMAL(12.3))
    plow = Column(DECIMAL(12.3))
    phigh = Column(DECIMAL(12.4))
    pchange = Column(DECIMAL(12.4))
    status = Column(CHAR(1))
    def __init__(self, sec):
        self.sdatetime = format(sec.lastupd)
        self.symbol = sec.symbol
        self.price = sec.price
        self.pchange = sec.change
        self.plow = sec.lo52w
        self.phigh = sec.hi52w
        self.status = 'A'

class User(Base):
    __tablename__ = 'users'

    id = Column(Integer, primary_key=True)
    name = Column(VARCHAR)
    fullname = Column(VARCHAR)
    password = Column(VARCHAR)

    def __repr__(self):
        return "<User(id='%d', name='%s', fullname='%s', password='%s')>" % (
        self.id, self.name, self.fullname, self.password)