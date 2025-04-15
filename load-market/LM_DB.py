import sqlalchemy
from sqlalchemy import create_engine, func, desc
from sqlalchemy.orm import sessionmaker
from Models import StockPrice, Portfolio
import sys, os, time

# dbconf="mysql://swang:VVKKll11@@@localhost/devx?charset=utf8mb4"
dbconf="mysql://swang:VVKKll11@@@localhost/prod?charset=utf8mb4"

engine = create_engine(dbconf, encoding='utf8', echo=False)
Session = sessionmaker(bind=engine)
session = Session()

def loadQuantity():
    rec = session.query(Portfolio.asof_time, Portfolio.symbol, Portfolio.quantity)\
        .filter_by(status='A').order_by(Portfolio.asof_time.desc()).limit(20)
    if rec is None: return None
    squan = dict()
    for row in rec:
        # print(row.asof_time, row.symbol, row.quantity)
        symbol = row.symbol
        quantity = row.quantity
        try:
            if squan[symbol] > 0: squan[symbol] += quantity
        except KeyError as ex:
            # print('ZZZ', ex)
            squan[symbol] = quantity
    # print(squan['MSFT'])
    # print(squan.keys())
    # print(squan.values())
    return squan
def getLastPrice(symbol):
    rec = session.query(StockPrice.price).filter_by(symbol=symbol,status='A').order_by(StockPrice.sdatetime.desc())
    if rec is None: return None
    lastrec = rec.first()
    # if lastrec == None: return 72.00
    # else: return lastrec[0]
    return lastrec[0]

def getTodaysHiLo(symbol):
    today = time.strftime('%Y-%m-%d')
    # print('today:%s'%today)
    # rec = session.query(StockPrice.plow, StockPrice.phigh).filter_by(symbol=symbol,sdatetime='today',status='A').order_by(StockPrice.sdatetime.desc())
    rec = session.query(StockPrice.sdatetime, StockPrice.plow, StockPrice.phigh).filter_by(symbol=symbol,status='A').order_by(StockPrice.sdatetime.desc())
    if rec is None: return None
    lastrec = rec.first()
    dt = str(lastrec[0])
    # print('dt:%s, dbdat:%s, isTrue:%s'%(dt, dt[0:10], dt[0:10] == today))
    if (dt[0:10] == today):
        # print('today:%s, dbdat:%s'%(today, dt[0:10]))
        return [float(lastrec[1]), float(lastrec[2])]
    else: return None

def getLastChange(symbol):
    # rec = session.query(StockPrice.id, StockPrice.pchange, StockPrice.price, StockPrice.status).filter_by(symbol=symbol,status='A').order_by(StockPrice.sdatetime.desc())
    rec = session.query(StockPrice.pchange).filter_by(symbol=symbol,status='A').order_by(StockPrice.sdatetime.desc())
    if rec is None: return None
    lastrec = rec.first()
    return lastrec[0]

# def addStockPrice(sec):
def saveSecToDB(sec):
    # print('===== saveToDB')
    dat_rec = session.query(StockPrice.id).filter_by(sdatetime=sec.lastupd,symbol=sec.symbol)
    if dat_rec.scalar() is None:
        dat = StockPrice(sec)
        session.add(dat)
        session.commit()
    session.close()

# def addStockPrice(sec):
#     dat_rec = session.query(StockPrice.id).filter_by(sdatetime=sec.lastupd,symbol=sec.symbol)
#     if dat_rec.scalar() is None:
#         # dlout(3, '+++ adding:StockPrice', sec.lastupd, sec.symbol, sec.price, sec.change, sec.lo52w, sec.hi52w)
#         # sec.show('+++')
#         show(sec, '+ ')
#         dat = StockPrice(sec)
#         session.add(dat)
#         session.commit()
#     else:
#         # dlout(3, '=== Security already exists for', sec.symbol, sec.lastupd, 'do nothing')
#         # sec.show('===')
#         show(sec, '= ')
#     # session.close()
