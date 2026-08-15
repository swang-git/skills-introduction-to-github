from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy import Column, Integer, SmallInteger, DateTime, CHAR, String, Date, DECIMAL
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


class recorded(Base):
    __tablename__ = 'recorded'
    recordedid = Column(Integer, primary_key=True)
    chanid = Column(Integer)
    starttime = Column(DateTime)
    endtime = Column(DateTime)
    title = Column(String(256))
    basename = Column(String(256))
    filesize = Column(Integer)
    watched = Column(SmallInteger)
    recgroup = Column(String(36))
    storagegroup = Column(String(16))
    # addtm = Column(DateTime)
    # status = Column(CHAR(1))

class channel(Base):
    __tablename__ = 'channel'
    chanid = Column(Integer, primary_key=True)
    channum = Column(String(6))

class channel_ALL(Base):
    __tablename__ = 'channel_old_id_ALL'
    chanid = Column(Integer, primary_key=True)
    channum = Column(String(6))

class reminders(Base):
    __tablename__ = 'reminders'
    id = Column(Integer, primary_key=True)
    due_date = Column(Date)
    recursive = Column(Integer)
    user_id = Column(Integer)
    tag = Column(VARCHAR)
    # filename = Column(VARCHAR)
    message = Column(VARCHAR)
    details = Column(TEXT)
    status = Column(CHAR(1))

    def __init__(self, rmd):
        self.due_date = rmd.due_date
        self.user_id = rmd.user_id
        self.recursive = rmd.recursive
        self.tag = rmd.tag
        self.message = rmd.message
        self.details = rmd.details
        # self.filename = rmd.filename
        self.status = rmd.status


class ChineseToGregorian(Base):
    __tablename__ = 'cal_chinese_to_gregorians'
    id = Column(Integer, primary_key=True)
    idx = Column(Integer)
    data = Column(VARCHAR)

    def __init__(self, idx, data):
        self.idx = idx
        self.data = data
