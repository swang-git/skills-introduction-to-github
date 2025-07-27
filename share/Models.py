from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy import Column, Integer, SmallInteger, DateTime, CHAR, String, Boolean
from sqlalchemy.dialects.mysql import VARCHAR, TEXT

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

class recorded(Base):
    __tablename__ = 'recorded'
    recordedid = Column(Integer, primary_key=True)
    chanid = Column(Integer)
    starttime = Column(DateTime)
    endtime = Column(DateTime)
    title = Column(String(256, convert_unicode=True))
    basename = Column(String(256, convert_unicode=True))
    filesize = Column(Integer)
    watched = Column(Integer)
    # addtm = Column(DateTime)
    # status = Column(CHAR(1))

class reminders(Base):
    __tablename__ = 'reminders'
    id = Column(Integer, primary_key=True)
    due_date = Column(DateTime)
    user_id = Column(Integer)
    status = Column(CHAR(1))
