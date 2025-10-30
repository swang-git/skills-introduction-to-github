import sys, re
from datetime import datetime
from Utils import padsp, isFloat, showConts, cleanMoney
from Bank import Statement, AccountActivity, StatementNotes
from BoaFunctions import getBalances, getDeposits, getWithdrawals, getBalancesForChecking2

class CheckingAccount(Statement):
  def __getattr__(self, key): return None
  def __init__(self, bank, yearMonth, filename):
    super().__init__(bank, yearMonth, filename)
    self.abal
    self.ebal

  def getData(self):
      self.setTxt()
      nxtstartline = 0
      date_pattern = '^for\s+(.*\s+\d{2},\s+\d{4})\s+to\s+(.*\d{2},\s\d{4})'
      lines = self.lines
      for i, line in enumerate(lines):
        # get start end dates
        match = re.search(date_pattern, line)
        if match:
          sdate = match.group(1)
          edate = match.group(2)
          self.sdate = datetime.strptime(sdate, '%B %d, %Y').strftime('%Y-%m-%d')
          self.edate = datetime.strptime(edate, '%B %d, %Y').strftime('%Y-%m-%d')
        elif line == 'Adv Tiered Interest Chkg':
          i += 1; self.anum1 = lines[i]
          i += 1; self.ebal1 = self.cleanMoney(lines[i])
          i += 3; self.anum2 = lines[i]
          i += 1; self.ebal2 = self.cleanMoney(lines[i])
          self.showDates()
          print('anum1[%s], anum2[%s]'%(self.anum1, self.anum2))
          print('ebal1[%s], ebal2[%s]' % (self.ebal1, self.ebal2))

        elif line == 'Your Adv Tiered Interest Chkg':
          acctName = 'Adv Tiered Interest Checking '
          [noteId, nxtstartline] = getBalances(self, i, 'Checking', acctName)
          # print('  XXSSSXX', startline)
          # print('abal[%s], ebal[%s]' % (self.abal1, self.ebal1))

        elif 'Deposits and other additions' == line:
          [nxtstartline, abal] = getDeposits(self, nxtstartline, 'Checking')
          nxtstartline = getWithdrawals(self, abal, nxtstartline, 'Checking')
          getBalancesForChecking2(self, nxtstartline, 'Checking', noteId)
          return
