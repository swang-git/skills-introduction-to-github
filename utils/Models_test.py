from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy import Column, Integer, SmallInteger, DateTime, CHAR, String
from sqlalchemy.dialects.mysql import VARCHAR, TEXT
from datetime import datetime

Base = declarative_base()

class User(Base):
    __tablename__ = 'users'

    id = Column(Integer, primary_key=True)
    name = Column(VARCHAR)
    fullname = Column(VARCHAR)
    password = Column(VARCHAR)

    def __repr__(self):
        return "<User(id='%d', name='%s', fullname='%s', password='%s')>" % (
        self.id, self.name, self.fullname, self.password)

class HomePage(Base):
    __tablename__ = 'homepage'
    id = Column(Integer, primary_key=True)
    tag = Column(CHAR(4))
    ymd = Column(Integer)
    cnt = Column(Integer)
    tit = Column(String(256, convert_unicode=True))
    flw = Column(Integer)
    fsz = Column(Integer)
    afz = Column(Integer)
    ffz = Column(Integer)
    img = Column(SmallInteger)
    vdo = Column(SmallInteger)
    addby = Column(String(16))
    addtm = Column(DateTime)
    # status = Column(CHAR(1))
    # def __init__(self, hp):
    #     self.tag = hp.tag
    #     self.ymd = hp.ymd
    #     self.cnt = hp.cnt
    #     self.tit = hp.tit
    #     self.flw = hp.flw
    #     self.fsz = hp.fsz
    #     self.ffz = hp.ffz
    #     self.img = hp.img
    #     self.vdo = hp.vdo
    #     self.addby = hp.addby
    #     self.addtm = datetime.now().strftime("%Y-%m-%d %H:%M:%S")

class ArtItem(Base):
    __tablename__ = 'art_items'
    __table_args__ = {'mysql_engine':'InnoDB'}
    id = Column(Integer, primary_key=True)
    idx = Column(Integer)
    pge = Column(Integer)
    tag = Column(CHAR(4))
    qid = Column(Integer)
    aut = Column(String(32, convert_unicode=True))
    tit = Column(String(256, convert_unicode=True))
    tim = Column(DateTime)
    clk = Column(Integer)
    flw = Column(Integer)
    nwd = Column(Integer)
    lnk = Column(String(256))
    def __init__(self, art):
        self.idx = art.idx
        self.pge = art.pge
        self.tim = art.tim
        self.tag = art.tag
        self.qid = art.qid
        self.tit = art.tit
        self.aut = art.aut
        self.lnk = art.lnk
        self.nwd = art.nwd
        self.flw = art.flw
        self.clk = art.clk
        self.status = 'A'
    
class DailyDat(Base):
    __tablename__ = 'DailyDat'
    __table_args__ = {'mysql_engine':'InnoDB'}
    id = Column(Integer, primary_key=True)
    idx = Column(Integer)
    tag = Column(CHAR(4))
    qid = Column(Integer)
    ymd = Column(Integer)
    aut = Column(String(32, convert_unicode=True))
    tit = Column(String(256, convert_unicode=True))
    tim = Column(DateTime)
    clk = Column(Integer)
    flw = Column(Integer)
    fsz = Column(Integer)
    afz = Column(Integer)
    ffz = Column(Integer)
    img = Column(SmallInteger)
    vdo = Column(SmallInteger)
    lnk = Column(String(256))
    # atm = Column(VARCHAR(32))
    jng = Column(CHAR(1))
    addby = Column(String(16))
    addtm = Column(DateTime)
    status = Column(CHAR(1))
    def __init__(self, art):
        self.idx = art.idx
        self.tag = art.tag
        self.ymd = art.ymd
        self.qid = art.qid
        self.tit = art.tit
        self.aut = art.aut
        self.tim = art.tim
        self.lnk = art.lnk
        self.afz = art.afz
        self.img = art.img
        self.vdo = art.vdo
        self.flw = art.flw
        self.fsz = art.fsz
        self.ffz = art.ffz
        self.clk = art.clk
        self.addby = 'PY3'
        self.status = 'A'
        self.addtm = datetime.now().strftime("%Y-%m-%d %H:%M:%S")

class DailyArt(Base):
    __tablename__ = 'DailyArt'
    id = Column(Integer, primary_key=True)
    idx = Column(Integer)
    tag = Column(CHAR(4))
    qid = Column(Integer)
    fid = Column(Integer)
    lvl = Column(SmallInteger)
    aut = Column(VARCHAR(32))
    tim = Column(DateTime)
    nwd = Column(Integer)
    addtm = Column(DateTime)
    status = Column(CHAR(1))
    txt = Column(TEXT(charset='utf8'))
    def __init__(self, art):
        self.idx = art.idx
        self.tag = art.tag
        self.qid = art.qid
        self.fid = art.fid
        self.lvl = art.lvl
        self.aut = art.aut
        self.tim = art.tim
        self.nwd = art.nwd
        self.txt = art.txt
        self.status = art.status
        self.addtm = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
