import sys, re
from datetime import datetime
from Utils import padsp, isFloat, showConts
# from Bank import Statement, AccountData, AccountSummary, Activity
from Bank import Statement, TotalAssets, AccountActivity, StatementNotes

class Accounts2019(Statement):
  def __getattr__(self, key): return None
  def __init__(self, bank, yearMonth, filename):
    super().__init__(bank, yearMonth, filename)
    self.activity_stop_pattern = '^A\s+monthly\s+Service\s+Fee\s+was'

  def getData(self):
    self.setTxtUsingPdfminer()
    # self.lineIter = iter(self.txt.splitlines())
    lines = iter(self.lines)
    self.setTranDetailLineNum()
    print(self.tranStart)
    # sys.exit(0)
    date_pattern = '^(.*\s+\d{2},\s+\d{4})through\s+(.*\d{2},\s+\d{4})'
    nextStart = 0
    for idx, line in enumerate(lines):
      # print('  XXXX', line)
      # get start end dates
      matchd = re.search(date_pattern, line)
      matchp = re.search('Primary Account:', line)
      if matchd and self.sdate == None:
        sdate = matchd.group(1)
        edate = matchd.group(2)
        self.sdate = datetime.strptime(sdate, '%B %d, %Y').strftime('%Y-%m-%d')
        self.edate = datetime.strptime(edate, '%B %d, %Y').strftime('%Y-%m-%d')
      elif matchp:
        # print('  MMMM', matchp.group(0))
        self.primaryAcct = next(lines)
        nextStart = idx
        break

    self.showDates()

    self.getTotalAssets()

    # print('-DG-', self.sdate, self.edate); sys.exit(0)
    if self.hasChkTran:
      self.getCheckingActivity(self.tranStart[0])

    self.getStatementNotes(self.tranStart[0] + 5)

    if self.hasSavTran:
      self.getSavingsActivity(self.tranStart[1] if self.hasChkTran else self.tranStart[0])

  def getTotalAssets(self):
    # lines = iter(self.lines)
    lines = self.lines
    for i, line in enumerate(lines):
      # if re.search('^ASSETS$', line):
      if re.search('^Checking\s+&\s+Savings$', line):
        start = i + 7
        acctNumC = lines[start]
        sbalC = re.sub('[$|,]', '', lines[start + 1])
        ebalC = re.sub('[$|,]', '', lines[start + 2])
        acctNumS = lines[start + 4]
        sbalS = re.sub('[$|,]', '', lines[start + 5])
        ebalS = re.sub('[$|,]', '', lines[start + 6])
        sbalT = re.sub('[$|,]', '', lines[start + 8])
        ebalT = re.sub('[$|,]', '', lines[start + 9])
        self.chkANum = acctNumC
        self.savANum = acctNumS
        self.hasChkTran = sbalC != ebalC
        self.hasSavTran = sbalS != ebalS
        self.totalAssets = TotalAssets(self.bank, self.year, self.month, sbalT, ebalT, 0, self.sdate, self.edate, self.primaryAcct)
        showConts('TotalAssets', self.totalAssets)
        if not self.hasChkTran:
          self.checkingActivity = []
          self.checkingActivity.append(AccountActivity(self.bank, self.year, self.month, sbalC, ebalC, 0, acctNumC, 'Checking', None, None, None))
          showConts('Checking Account Activity 0', self.checkingActivity[0])
        if not self.hasSavTran:
          self.savingsActivity = []
          self.savingsActivity.append(AccountActivity(self.bank, self.year, self.month, sbalS, ebalS, 0, acctNumS, 'Savings', None, None, None))
          showConts('Savings Account Activity 0', self.savingsActivity[0])
        # print('-DG-', acctNumC, sbalC, ebalC, acctNumS, sbalS, ebalS, sbalT, ebalT); sys.exit(0)
        return

  def setTranDetailLineNum(self):
    self.tranStart = []
    for i, line in enumerate(self.lines):
      if len(self.tranStart) == 2: return
      # if 'TRANSACTION DETAIL' in line: self.tranStart.append(i - 20)
      if 'TRANSACTION DETAIL' in line: self.tranStart.append(i)

  def getCheckingActivity(self, start):
    # print(' ---- getChkActivity', start)
    self.checkingActivity = []
    lines = iter(self.lines[start:])
    tran = 1
    for i, line in enumerate(lines):
    #   print(' SSSS', i, line)
      if re.search('^TRANSACTION\s+DETAIL$', line):
        # print(' XXXX savings tran start', i, line)
        next(lines)
        next(lines)
        next(lines)
        next(lines)
        sbal = re.sub('[$|,]', '', next(lines))
        while True:
          line = next(lines)
          if 'Ending Balance' in line:
            return i
          date = self.year + '-' + line.replace('/', '-')
          desc = re.sub('\s+', ' ', next(lines))
          amnt = next(lines)
          ebal = re.sub('[$|,]', '', next(lines))
          actv = AccountActivity(self.bank, self.year, self.month, sbal, ebal, tran, self.chkANum, 'Checking', date, desc, amnt)
          self.totalAssets.setTran(1)
          self.checkingActivity.append(actv)
          showConts('Checking Account Activities ' + str(tran), actv)
          tran += 1
          sbal = ebal
        return
      # print('  XXX', i, line)

  def getStatementNotes(self, start):
    # print(' ---- getStatementNotes', start)
    lines = iter(self.lines[start:])
    self.statementNotes = []
    ni = 1
    for i, line in enumerate(lines):
      # print(' SSSS', i, line)
      if line == 'TRANSACTION DETAIL': return i
      if re.search('^SAVINGS\s+SUMMARY$', line):
        # print(' XXXX statement notes start', i, line)
        next(lines); i += 1
        next(lines); i += 1
        next(lines); i += 1
        next(lines); i += 1
        next(lines); i += 1
        next(lines); i += 1
        note = next(lines).strip()
        amnt = re.sub('[%|$]', '', next(lines).strip())
        snt = StatementNotes(self.bank, self.year, self.month, note, amnt)
        self.statementNotes.append(snt)
        showConts('Statement Notes ' + str(ni), snt)
        note = next(lines).strip()
        amnt = re.sub('[%|$]', '', next(lines).strip())
        snt = StatementNotes(self.bank, self.year, self.month, note, amnt)
        self.statementNotes.append(snt); ni += 1
        showConts('Statement Notes ' + str(ni), snt)
        note = next(lines).strip()
        amnt = re.sub('[%|$]', '', next(lines).strip())
        snt = StatementNotes(self.bank, self.year, self.month, note, amnt)
        self.statementNotes.append(snt); ni += 1
        showConts('Statement Notes ' + str(ni), snt)

  def getSavingsActivity(self, start):
    # print(' ---- getSavingsActivity', start)
    self.savingsActivity = []
    lines = iter(self.lines[start:])
    tran = 1
    for i, line in enumerate(lines):
      # print(' SSSS', i, line)
      if re.search('^TRANSACTION\s+DETAIL$', line):
        # print(' XXXX savings tran start', i, line)
        next(lines)
        # next(lines)
        next(lines)
        next(lines)
        next(lines)
        sbal = re.sub('[$|,]', '', next(lines))
        while True:
          line = next(lines)
          if 'Ending Balance' in line:
            return
          date = self.year + '-' + line.replace('/', '-')
          desc = re.sub('\s+', ' ', next(lines))
          amnt = next(lines)
          ebal = re.sub('[$|,]', '', next(lines))
          actv = AccountActivity(self.bank, self.year, self.month, sbal, ebal, tran, self.savANum, 'Savings', date, desc, amnt)
          self.savingsActivity.append(actv)
          self.totalAssets.setTran(1)
          showConts('Savings Account Activities ' + str(tran), actv)
          tran += 1
          sbal = ebal
        return
      # print('  XXX', i, line)

  def getSectionsIdx(self):
    # self.setTxt()
    sec_patt ='^TRANSACTION\s+DETAIL$'
    self.secIdx = []
    for i, line in enumerate(self.lines):
      # print(' XXX', i, line)
      if re.search(sec_patt, line):
        self.secIdx.append(i)
    # print(self.secIdx)
    for i in self.secIdx: print(i, self.lines[i])
