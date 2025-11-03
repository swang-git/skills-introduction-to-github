import sqlalchemy
from sqlalchemy import create_engine, func, desc
from sqlalchemy.orm import sessionmaker
from BankModels import BankStatementAssetsModel, BankAccountActivitiesModel, BankStatementNotesModel
from BankModels import BankStatementHoldingsModel
from BankModels import FidelityAccountActivitiesModel
from Utils import padsp
import sys

##dbconf="mysql://swang:VVKKll11@@@localhost/prod?charset=utf8mb4"
dbconf="mysql://swang:VVKKll11@@@localhost/devx?charset=utf8mb4"

engine = create_engine(dbconf, encoding='utf8', echo=False)
Session = sessionmaker(bind=engine)
session = Session()

def addFidelityAccountActivity(d):
  chk = session.query(FidelityAccountActivitiesModel).filter_by(user_id=1, bank=d[0], year=d[1], month=d[2], account_num=d[4], idx=d[5], security=d[7])
  if chk.scalar():
    print('\t\t\t --x-- -- The record %s %s %s %s %d %s %s %s %s\t exists, skip ...' % (d[0], d[1], d[2], d[4], d[5], padsp(d[6], 6), padsp(d[7], 6), padsp(d[11], 8), d[10]))
    return
  print('\t\t\t +++++ Adding record %s %s %s %s %d %s %s %s %s +++++' % (d[0], d[1], d[2], d[4], d[5], padsp(d[6], 6), d[7], padsp(d[11], 8), d[10]))
  session.add(FidelityAccountActivitiesModel(d))
  session.commit()

def checkToAdd(tag, chk, row, dat, i=0):
  if chk.scalar() is None:
    if row.bank == 'BOA': print(i, tag, row.bank, row.year, row.mnth, row.tran, row.primaryAcct, row.balB, row.balE)
    else: print(i, tag, row.bank, row.year, row.mnth, row.anum, row.balB, row.balE)
    session.add(dat)
    session.commit()
    return True
  else:
    if row.bank == 'BOA': print('%d, record: [%s, %s, %s, %d %s] "exists for %s", skip ...' %(i, row.bank, row.year, row.mnth, row.tran, row.primaryAcct, tag))
    else: print('%d, record: [%s, %s, %s, %s, %s] "exists for %s", skip ...' %(i, row.bank, row.year, row.mnth, row.anum, row.symb, tag))
    return False

def addAssets(row):
  # print(row)
  # sys.exit(0)
  # chk = session.query(table).filter_by(bank=row.bank, year=row.year, month=row.mnth)
  chk = session.query(BankStatementAssetsModel).filter_by(bank=row.bank, year=row.year, month=row.mnth)
  # if chk.scalar():
    # print('asset %s, %s, %s, %s exists, skip' % (row.bank, row.dateB, row.tran, row.balE))
  #   return
  dat = BankStatementAssetsModel(row)
  tag = 'Adding Assets'
  checkToAdd(tag, chk, row, dat, 1)

def addActivities(idx, row):
  date = row.date
  if date == None: date = '00-00-00'
  chk = session.query(BankAccountActivitiesModel).filter_by(bank=row.bank, year=row.year, month=row.mnth, account_num=row.aNum, tran_num=row.tran)
  if chk.scalar():
    print('%d activity %s, %s, %s, %s exists, skip' % (idx, date, row.aNum, row.tran, row.type))
    return
  else:
    dat = BankAccountActivitiesModel(row)
    session.add(dat)
    session.commit()

def addNotes(idx, row):
  # print('saving notes', row)
  chk = session.query(BankStatementNotesModel).filter_by(bank=row.bank, year=row.year, month=row.month, note_id=row.noteId)
  if chk.scalar():
    print('%d notes %d, %s, %s exists, skip' % (idx, row.noteId, row.amnt, row.notes))
    return
  else:
    print('Addng %d notes %d, %s, %s' % (idx, row.noteId, row.amnt, row.notes))
    dat = BankStatementNotesModel(row)
    session.add(dat)
    session.commit()

def addHoldings(i, row):
  # chk = session.query(BankStatementHoldingsModel).filter_by(bank=row.bank, year=row.year, month=row.mnth, account_num=row.anum, symbol=row.symb)
  chk = session.query(BankStatementHoldingsModel).filter_by(bank=row[0], year=row[1], month=row[2], account_num=row[4], symbol=row[len(row) - 1])
  if chk.scalar() is None:
    # print('saving holdings', i+1, row.bank, row.year, row.mnth, row.anam, row.symb, row.balE)
    print('saving holdings', i+1, len(row), row)
    da = BankStatementHoldingsModel(row)
    session.add(da)
    session.commit()
    return
  else:
    # print('[%s], record[%s], [%s], [%s], [%s], [%s] exists, skip' %(i+1, row.bank, row.year, row.mnth, row.anum, row.symb))
    # print('[%s], record[%s], [%s], [%s], [%s], [%s] exists, skip' %(i+1, row[0], row[1], row[2], row[4], row[10]))
    # print('%s record[%s],[%s] exists, skip' %(i, row.bank, row.year))
    return False
