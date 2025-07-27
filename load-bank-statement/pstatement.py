#!/usr/bin/python
# import fitz
from datetime import datetime
import sys, re
from Utils import my_argparse, padsp, showConts, showActivity, showNotes, showHolding
from BoaSavingsAccount import SavingsAccount
from BoaCheckingAccount import CheckingAccount
from BoaStatements import BoaStatements
from ChaseStatement import Accounts # work for 2020 except 202008 and 2021
from ChaseStatement2019 import Accounts2019
from ChaseStatement202008 import Accounts208
from BankDB import addAssets, addActivities, addNotes, addHoldings, addFidelityAccountActivity
from Bank import Holding, bym
from BankModels import BankStatementAssetsModel, BankAccountActivitiesModel, BankStatementNotesModel
from BankModels import BankStatementHoldingsModel

from FidelityIRA import IRAAndIndividualAccounts
from FidelityRoth import RothAndIndividualAccounts
from FidelityStatements import FidelityStatements
from FidelityAnnuity import AnnuityAccount

# print('\t\tstarting process bank\n')

args = my_argparse()
ym = args.yermon
bank = args.ccbank
year = ym[0:4]
mnth = ym[4:]
bymList = [bank, ym[0:4], ym[4:]]
acctType = args.actype

print('Processing %s statement for month %s account %s'%(bank, ym, acctType))

if bank == 'Fidelity':
  if acctType == 'roth':stmt = RothAndIndividualAccounts(bank, ym, '_roth.html')
  elif acctType == 'ira': stmt = IRAAndIndividualAccounts(bank, ym, '_ira.html')
  elif acctType == 'ann': stmt = AnnuityAccount(bank, ym, '_annuity.html')
  else: stmt = FidelityStatements(bank, ym)
elif bank == 'BOA':
  if acctType == 'checking': stmt = CheckingAccount(bank, ym, '_checking.pdf')
  elif acctType == 'savings':stmt =  SavingsAccount(bank, ym, '_savings.pdf')
  else: stmt = BoaStatements(bank, ym)
elif bank == 'Chase':
  if ym == '202008':
    print('instanciating Account208')
    stmt = Accounts208(bank, ym, '.pdf')
  elif re.search('2020\d{2}', ym):
    stmt = Accounts(bank, ym, '.pdf')
  elif re.search('2019\d{2}', ym):
    print('instanciating Account19')
    stmt = Accounts2019(bank, ym, '.pdf')
  elif re.search('2021\d{2}', ym):
    print('instanciating Account for 2021')
    stmt = Accounts(bank, ym, '.pdf')

# stmt.showTxt(); sys.exit(0)
stmt.getData()  #;sys.exit(0)
if bank == 'Fidelity':
  print('\n\t\t ---- The above is output from Fidelity Functions ----')
  # print('\n\t\t ---- Fidelity Total Assets----')
  totalAssets = stmt.totalAssets
  showConts('Show Fidelity Total Assets', totalAssets)
  addAssets(totalAssets)

  print('\n\t\t ---- Show Fidelity Holdings ----')
  for i, x in enumerate(stmt.holdings):
    Holding(bym(bank, year, mnth), x).show()
  # #   #___ print("%s %s"%(padsp(str(i+1), 2), bymList + x))
    added = addHoldings(i, bymList + x)
  #   if added: print("%s %s"%(padsp(str(i), 2), x))

  # print('\n\t\t\t ---- Fidelity Investment Activities ----')
  print('\n\t\t ---- Show Fidelity Investment / Bill Payment / Deposit / Withdrawals Activities ----')
  for i, a in enumerate(stmt.accountActivity):
    print('%s %s'%(padsp(i, 2), a))
    addFidelityAccountActivity(a)
  sys.exit(0)
elif bank == 'Chase': # there is only one combined statement for checking and savings
  showConts('Total Assets', stmt.totalAssets)
  addAssets(stmt.totalAssets)
  for i, tran in enumerate(stmt.checkingActivity):
    showConts('Checking Account Activity ' + str(i + 1), tran)
    addActivities(i, tran)
  for i, tran in enumerate(stmt.savingsActivity):
    showConts('Savings Account Activity ' + str(i + 1), tran)
    addActivities(i, tran)
  for i, note in enumerate(stmt.statementNotes):
    note.noteId = i + 1
    showConts('Statement Notes ' + str(i + 1), note)
    addNotes(i + 1, note)
elif bank == 'BOA' and acctType == 'both':
  showConts('Total Assets', stmt.totalAssets)  # for BOA it needs both statements to get Total Assets/All Activity/All Notes
  addAssets(stmt.totalAssets)
  for trani, actv in enumerate(stmt.allActivity):
    showConts('All Activity ' + str(trani + 1), actv)
    addActivities(trani + 1, actv)
  for trani, note in enumerate(stmt.allNotes):
    note.noteId = trani + 1
    showConts('All Notes ' + str(trani + 1), note)
    addNotes(trani + 1, note)
elif bank == 'BOA':
  print('You have to run with option "-t both" to have stmt.totalAssets/allActivity/allNotes')
  for i, note in enumerate(stmt.statementNotes): showNotes(i + 1, note)
    # showConts('Statement Notes %d for %s' % (i + 1, acctType), note)
  if acctType == 'savings':
    for i, tran in enumerate(stmt.savingsActivity): showConts('Account Activity %d for %s'%(i + 1, acctType), tran)
  if acctType == 'checking':
    # for i, tran in enumerate(stmt.accountActivity): showActivity('Account Activity %d for %s'%(i + 1, acctType), tran)
    for i, tran in enumerate(stmt.accountActivity): showActivity(tran)
