from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy import Column, Integer, SmallInteger, DateTime, CHAR, String, Numeric
from sqlalchemy.dialects.mysql import VARCHAR, TEXT

Base = declarative_base()

class GCard(Base):
    __tablename__ = 'gift_cards'
    id = Column(Integer, primary_key=True)
    spend_datetime = Column(DateTime)
    spend_id = Column(Integer)
    pay_method_id = Column(Integer)
    balance = Column(Numeric(10, 2))
    card_num = Column(String(45, convert_unicode=True))
    status = Column(CHAR(1))
    created_at = Column(DateTime)
    updated_at = Column(DateTime)

class Spend(Base):
    __tablename__ = 'spends'
    id = Column(Integer, primary_key=True)
    purchasedon = Column(DateTime)
    totalpaid = Column(Numeric(10, 2))
    paymethod_id = Column(Integer)
    status = Column(CHAR(1))
    created_at = Column(DateTime)
    updated_at = Column(DateTime)

    # def __repr__(self):
    #     return "<User(id='%d', name='%s', fullname='%s', password='%s')>" % (
    #     self.id, self.name, self.fullname, self.password)

class recorded(Base):
    __tablename__ = 'recorded'
    recordedid = Column(Integer, primary_key=True)
    chanid = Column(Integer)
    starttime = Column(DateTime)
    endtime = Column(DateTime)
    title = Column(String(256, convert_unicode=True))
    basename = Column(String(256, convert_unicode=True))
    filesize = Column(Integer)
    recgroup = Column(String(36, convert_unicode=True))
    # addtm = Column(DateTime)
    # status = Column(CHAR(1))

class reminders(Base):
    __tablename__ = 'reminders'
    id = Column(Integer, primary_key=True)
    due_date = Column(DateTime)
    user_id = Column(Integer)
    status = Column(CHAR(1))
