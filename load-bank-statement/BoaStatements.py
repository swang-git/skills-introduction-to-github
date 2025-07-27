import sys, re
from datetime import datetime
from Utils import padsp, isFloat, showConts
from Bank import Statement, StatementNotes, TotalAssets
from BoaSavingsAccount import SavingsAccount
from BoaCheckingAccount import CheckingAccount

class BoaStatements(Statement):
  def __getattr__(self, key): return None
  def __init__(self, bank, yearMonth):
    super().__init__(bank, yearMonth, None)
    self.bank = bank
    self.yearMonth = yearMonth
    self.chkstmt = CheckingAccount(bank, yearMonth, '_checking.pdf')
    self.savstmt = SavingsAccount(bank, yearMonth, '_savings.pdf')
    self.allActivity
    self.allNotes

  def getData(self):
    self.chkstmt.getData()
    self.savstmt.getData()
    self.abal = float(self.chkstmt.abal1) + float(self.chkstmt.abal2) + float(self.savstmt.abal1)
    self.ebal = float(self.chkstmt.ebal1) + float(self.chkstmt.ebal2) + float(self.savstmt.ebal1)
    primaryAcct = self.chkstmt.anum1
    # self.tran_cnt = self.chkstmt.tran + self.savstmt.tran
    self.tran_cnt = len(self.chkstmt.accountActivity) + len(self.savstmt.accountActivity)
    # print('\t\t++++++++++', len(self.chkstmt.accountActivity), len(self.savstmt.accountActivity))
    dateB = self.chkstmt.sdate
    dateE = self.chkstmt.edate
    self.totalAssets = TotalAssets(self.bank, self.year, self.month, self.abal, self.ebal, self.tran_cnt, dateB, dateE, primaryAcct)
    # showConts('Total Assets', self.totalAssets)
    self.allActivity = self.chkstmt.accountActivity + self.savstmt.accountActivity
    self.allNotes = self.chkstmt.statementNotes + self.savstmt.statementNotes


    # self.statementNotes = self.chkstmt.statementNotes + self.savstmt.statementNotes
    # for i, p in enumerate(self.chkstmt.statementNotes): showConts('All Checking Statement Notes ' + str(i + 1), p)
    # for i, p in enumerate(self.savstmt.statementNotes): showConts('All Savings Statement Notes ' + str(i + 1), p)
