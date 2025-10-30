from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy import Column, Integer, SmallInteger, DateTime, CHAR, String, FLOAT, DECIMAL
from sqlalchemy.dialects.mysql import VARCHAR, TEXT

Base = declarative_base()

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