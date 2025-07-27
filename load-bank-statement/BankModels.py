from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy import Column, Integer, Date,DateTime, SmallInteger, CHAR, String, Numeric
from sqlalchemy.dialects.mysql import VARCHAR, TEXT
# from datetime import datetime

Base = declarative_base()

class BankStatementAssetsModel(Base):
    __tablename__ = 'bank_statement_assets'
    __table_args__ = {'mysql_engine':'InnoDB'}
    id = Column(Integer, primary_key=True)
    bank = Column(VARCHAR(8))
    year = Column(SmallInteger)
    month = Column(SmallInteger)
    begin_date = Column(Date)
    end_date = Column(Date)
    primary_account = Column(CHAR(24))
    begin_balance = Column(Numeric(10,2))
    end_balance = Column(Numeric(10, 2))
    tran_cnt = Column(SmallInteger)

    def __init__(self, da):
        self.bank = da.bank
        self.year = da.year
        self.month = da.mnth
        self.begin_date = da.dateB
        self.end_date = da.dateE
        self.primary_account = da.primaryAcct
        self.begin_balance = da.balB
        self.end_balance = da.balE
        self.tran_cnt = da.tran

class BankAccountActivitiesModel(Base):
    __tablename__ = 'bank_account_activities'
    __table_args__ = {'mysql_engine':'InnoDB'}
    id = Column(Integer, primary_key=True)
    bank = Column(VARCHAR(8))
    year = Column(SmallInteger)
    month = Column(SmallInteger)
    account_num = Column(CHAR(15))
    acct_type = Column(VARCHAR(8))
    tran_num = Column(SmallInteger)
    tran_date = Column(Date)
    description = Column(VARCHAR(60))
    begin_balance = Column(Numeric(10,2))
    amount = Column(Numeric(8,2))
    end_balance = Column(Numeric(10, 2))

    def __init__(self, da):
        self.bank = da.bank
        self.year = da.year
        self.month = da.mnth
        self.account_num = da.aNum
        self.acct_type = da.type
        self.tran_date = da.date
        self.tran_num = da.tran
        self.description = da.desc
        self.begin_balance = da.balB
        self.amount = da.amnt
        self.end_balance = da.balE

class BankStatementNotesModel(Base):
    __tablename__ = 'bank_statement_notes'
    __table_args__ = {'mysql_engine':'InnoDB'}
    id = Column(Integer, primary_key=True)
    bank = Column(VARCHAR(8))
    year = Column(SmallInteger)
    month = Column(SmallInteger)
    notes = Column(VARCHAR(80))
    note_id = Column(SmallInteger)
    amount = Column(Numeric(8,2))

    def __init__(self, da):
        self.bank = da.bank
        self.year = da.year
        self.month = da.month
        self.note_id = da.noteId
        self.notes = da.notes
        self.amount = da.amnt

class BankStatementHoldingsModel(Base):
    __tablename__ = 'bank_statement_holdings'
    __table_args__ = {'mysql_engine':'InnoDB'}
    id = Column(Integer, primary_key=True)
    user_id = Column(SmallInteger)
    bank = Column(VARCHAR(8))
    year = Column(SmallInteger)
    month = Column(SmallInteger)
    account_num = Column(VARCHAR(16))
    account_name = Column(VARCHAR(16))
    symbol = Column(VARCHAR(8))
    start_balance = Column(Numeric(12,2))
    end_balance = Column(Numeric(12,2))
    price = Column(Numeric(12,6))
    quantity = Column(Numeric(10,3))
    cost = Column(Numeric(10,3))

    def __init__(self, da):
        self.user_id = 1
        self.bank = da[0]
        self.year = da[1]
        self.month = da[2]
        self.account_name = da[3]
        self.account_num = da[4]
        self.quantity = da[5]
        self.price = da[6]
        self.start_balance = da[7]
        self.end_balance = da[8]
        self.cost = da[9]
        self.symbol = da[10]
        # self.bank = da.bank
        # self.year = da.year
        # self.month = da.mnth
        # self.account_num = da.anum
        # self.account_name = da.anam
        # self.symbol = da.symb
        # self.start_balance = da.balB
        # self.price = da.pric
        # self.quantity = da.quan
        # self.end_balance = da.balE
        # self.cost = da.cost
class FidelityAccountActivitiesModel(Base):
    __tablename__ = 'fidelity_account_activities'
    __table_args__ = {'mysql_engine':'InnoDB'}
    id = Column(Integer, primary_key=True)
    user_id = Column(SmallInteger)
    bank = Column(VARCHAR(8))
    year = Column(SmallInteger)
    month = Column(SmallInteger)
    account_name = Column(VARCHAR(16))
    account_num = Column(VARCHAR(16))
    idx = Column(SmallInteger)
    sett_date = Column(Date)
    security = Column(VARCHAR(16))
    description = Column(VARCHAR(45))
    quantity = Column(Numeric(10,3))
    price = Column(Numeric(12,6))
    amount = Column(Numeric(12,2))
    cost = Column(Numeric(10,3))

    def __init__(self, da):
        self.user_id = 1
        self.bank = da[0]
        self.year = da[1]
        self.month = da[2]
        self.account_name = da[3]
        self.account_num = da[4]
        self.idx = da[5]
        self.sett_date = da[6]
        self.security = da[7]
        self.description = da[8]
        self.quantity = da[9]
        self.price = da[10]
        self.amount = da[11]
        self.cost = da[12]