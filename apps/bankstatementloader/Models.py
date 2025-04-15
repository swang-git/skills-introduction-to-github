from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy import Column, Integer, Float, DateTime
from sqlalchemy.dialects.mysql import VARCHAR

Base = declarative_base()

class BankStatementAsset(Base):
    __tablename__ = 'bank_statement_assets'
    id = Column(Integer, primary_key=True)
    user_id = Column(Integer)
    bank = Column(VARCHAR)
    year = Column(Integer)
    month = Column(Integer)
    begin_date = Column(DateTime)
    end_date = Column(DateTime)
    primary_account = Column(VARCHAR)
    tran_cnt = Column(Integer)
    begin_balance = Column(Float)
    end_balance = Column(Float)

    def __repr__(self):
      return ("BankStatementAsset(use_id={}, bank={}, year={}, month={})".format(self.user_id, self.bank, self.year, self.month))

class BankStatementNote(Base):
    __tablename__ = 'bank_statement_notes'
    id = Column(Integer, primary_key=True)
    user_id = Column(Integer)
    bank = Column(VARCHAR)
    year = Column(Integer)
    month = Column(Integer)
    note_id = Column(Integer)
    notes = Column(VARCHAR)
    amount = Column(Float)

    def __repr__(self):
      return ("BankStatementNote(use_id={}, bank={}, year={}, month={})".format(self.user_id, self.bank, self.year, self.month))
    
class BankAccountActivity(Base):
    __tablename__ = 'bank_account_activities'
    id = Column(Integer, primary_key=True)
    user_id = Column(Integer)
    bank = Column(VARCHAR)
    year = Column(Integer)
    month = Column(Integer)
    account_num = Column(VARCHAR)
    acct_type = Column(VARCHAR)
    tran_num = Column(Integer)
    tran_date = Column(DateTime)
    description = Column(VARCHAR)
    begin_balance = Column(Float)
    amount = Column(Float)
    end_balance = Column(Float)

    def __repr__(self):
      return ("BankAccountActivity(use_id={}, bank={}, year={}, month={}) tran_date".format(self.user_id, self.bank, self.year, self.month, self.tran_date))
