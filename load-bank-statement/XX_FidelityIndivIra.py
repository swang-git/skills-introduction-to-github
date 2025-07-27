import sys, re
from datetime import datetime
from Utils import padsp, isFloat, showConts
from Bank import Statement, StatementNotes
from BoaFunctions import getBalances, getDeposits, getWithdrawals
from FidelityFunctions import *

class IndivIraAccounts(Statement):
  def __getattr__(self, key): return None
  def __init__(self, bank, yearMonth, filename):
    super().__init__(bank, yearMonth, filename)

  def getData(self):
    self.setTxt()
    self.checkTranCost()
    portval = yourPortfolioValue(iter(self.lines))
    print(portval)
    stmtatv, yearatv = ThisPeriodAndYearToDate(iter(self.lines))
    print(stmtatv)
    print(yearatv)
    endvals = EndingPortfolioValue(iter(self.lines))
    print(endvals)
    # assert portval['value'] == endvals.get('stmt')
    assert portval.get('value') == endvals.get('stmt')

    return
    # self.setTxt()
    # date_pattern = '^for\s+(.*\s+\d{2},\s+\d{4})\s+to\s+(.*\d{2},\s+\d{4})'
    # lines = self.lines
    # acctName = 'Money Market Savings '
    # for i, line in enumerate(lines):
    #   match = re.search(date_pattern, line)
    #   if match:
    #     sdate = match.group(1)
    #     edate = match.group(2)
    #     self.sdate = datetime.strptime(sdate, '%B %d, %Y').strftime('%Y-%m-%d')
    #     self.edate = datetime.strptime(edate, '%B %d, %Y').strftime('%Y-%m-%d')
    #     self.showDates()

    #   elif 'Account number:' in line:
    #     self.anum1 = line.replace('Account number:', '').strip()
    #     print('anum1[%s]' % (self.anum1))

    #   elif line == 'Account summary':
    #     # print('  CCCX', i, line)
    #     nxtstartline = getBalances(self, i, 'Savings', acctName)
    #     print('abal[%s], ebal[%s]' % (self.abal, self.ebal))
    #     # sys.exit(0)
    #   elif 'Deposits and other additions' == line:
    #     [nxtstartline, abal] = getDeposits(self, nxtstartline, 'Savings')
    #     nxtstartline = getWithdrawals(self, abal, nxtstartline, 'Savings')
    #     return
