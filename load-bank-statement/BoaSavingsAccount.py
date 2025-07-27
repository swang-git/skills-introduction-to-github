import sys, re
from datetime import datetime
from Utils import padsp, isFloat, showConts
from Bank import Statement, StatementNotes
from BoaFunctions import getBalances, getDeposits, getWithdrawals

class SavingsAccount(Statement):
  def __getattr__(self, key): return None
  def __init__(self, bank, yearMonth, filename):
    super().__init__(bank, yearMonth, filename)

  def getData(self):
    self.setTxt()
    date_pattern = '^for\s+(.*\s+\d{2},\s+\d{4})\s+to\s+(.*\d{2},\s+\d{4})'
    lines = self.lines
    acctName = 'Money Market Savings '
    for i, line in enumerate(lines):
      match = re.search(date_pattern, line)
      if match:
        sdate = match.group(1)
        edate = match.group(2)
        self.sdate = datetime.strptime(sdate, '%B %d, %Y').strftime('%Y-%m-%d')
        self.edate = datetime.strptime(edate, '%B %d, %Y').strftime('%Y-%m-%d')
        # print('sdate[%s], edate[%s]'%(self.sdate, self.edate))
        self.showDates()

      elif 'Account number:' in line:
        self.anum1 = line.replace('Account number:', '').strip()
        print('anum1[%s]' % (self.anum1))

      elif line == 'Account summary':
        # print('  CCCX', i, line)
        noteId, nxtstartline = getBalances(self, i, 'Savings', acctName)
        print('abal[%s], ebal[%s]' % (self.abal, self.ebal))
        # sys.exit(0)
      elif 'Deposits and other additions' == line:
        [nxtstartline, abal] = getDeposits(self, nxtstartline, 'Savings')
        nxtstartline = getWithdrawals(self, abal, nxtstartline, 'Savings')
        return
