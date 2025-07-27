import sys, re
from Bank import StatementNotes, AccountActivity
from Utils import showConts, cleanMoney, isFloat

def getBalances(self, startline, acctType, acctName):
  noteId = 1
  nt = StatementNotes(self.bank, self.year, self.month, noteId, acctName + self.anum1, None)
  self.statementNotes.append(nt)
  showConts(acctType + ' Statement Notes %d'%noteId, nt)
  lines = self.lines[startline:]
  for i, line in enumerate(lines):
    if re.search('^Beginning balance on', line):
      self.abal1 = cleanMoney(lines[i + 1])
    elif re.search('^Ending balance on', line):
      self.ebal1 = cleanMoney(lines[i + 1])
      [note, amnt] = lines[i + 2].split(':')
      amnt = float(cleanMoney(amnt))
      if amnt != 0:
        noteId += 1
        nt = StatementNotes(self.bank, self.year, self.month, noteId, note, amnt)
        self.statementNotes.append(nt)
        showConts(acctType + ' Statement Notes %d'%noteId, nt)

      [note, amnt] = lines[i + 3].split(':')
      amnt = float(cleanMoney(amnt))
      if amnt != 0:
        noteId += 1
        nt = StatementNotes(self.bank, self.year, self.month, noteId, note, amnt)
        self.statementNotes.append(nt)
        showConts(acctType + ' Statement Notes %d'%noteId, nt)
      # print('    XXXX startline', i + 3 + startline)
      return [noteId, i + 3 + startline]
    elif line == 'Checks':
      amnt = float(lines[i + 1])
      if amnt != 0:
        noteId += 1
        nt = StatementNotes(self.bank, self.year, self.month, noteId, 'Checks', amnt)
        self.statementNotes.append(nt)
        showConts(acctType + ' Statement Notes %d'%noteId, nt)
    elif line == 'Service fees':
      amnt = float(lines[i + 1])
      if amnt != 0:
        noteId += 1
        nt = StatementNotes(self.bank, self.year, self.month, noteId, 'Service fees', amnt)
        self.statementNotes.append(nt)
        showConts(acctType + ' Statement Notes %d'%noteId, nt)

def getWithdrawals(self, abal, startline, acctType):
  # print(' --- getWithdrawals', startline, line)
  ebal = abal
  lines = self.lines[startline:]
  for i, line in enumerate(lines):
    if 'Withdrawals and other subtractions' in line:
      i += 3
      while True:
        if 'Total withdrawals and other subtractions' in line:
          try:
            assert round(float(ebal), 2) == round(float(self.ebal1), 2)
          except:
            print('AssertionError: ebal == self.ebal1:', ebal, '<>', self.ebal1)
            sys.exit(0)
          return i + 4 + startline
        datx = lines[i + 1]
        desc = lines[i + 2]
        amnt = lines[i + 3]
        if not isFloat(amnt):
          desc += ' ' + amnt
          amnt = lines[i + 4]
          line = lines[i + 5]
          i += 4
        else:
          line = lines[i + 4]
          i += 3
        [mm, dd, yycc] = datx.split('/')
        date = yycc + '-' + mm + '-' + dd
        ebal += float(amnt)
        # print('--WITHDRAWALS', i, startline, startline + i, 'date:[%s]'%date, 'desc:[%s]'%desc, 'amnt[%s]'%amnt, 'nxt_line:[%s]'%line);  #sys.exit(0)
        if float(amnt) == 0: amnt = '0.00'
        actv = AccountActivity(self.bank, self.year, self.month, abal, ebal, self.tran, self.anum1, acctType, date, desc, amnt)
        abal = ebal
        self.accountActivity.append(actv)
        showConts(acctType + ' Activity ' + str(self.tran), actv)
        self.tran += 1

def getDeposits(self, startline, acctType):
  lines = self.lines[startline:]
  for i, line in enumerate(lines):
    if 'Deposits and other additions' in line:
      i += 3
      totalAmnt = 0.0
      totalDeposits = 0.0
      # self.tran += 1
      abal = float(self.abal1)
      ebal = float(self.abal1)
      while True:
        # print('  XXXX', i, line)
        if 'Total deposits and other additions' in line:
          print('stop and returning at ', i + 4 + startline, line)
          totalDeposits = float(cleanMoney(lines[i + 2]))
          try:
            assert round(totalAmnt, 2) == round(float(totalDeposits), 2)
          except:
            print('AssertionError totalAmnt == totalDeposits BUT', str(totalAmnt) + ' <> ' + str(totalDeposits))
          return [i + 3 + startline, ebal]
        datx = lines[i + 1]
        desc = lines[i + 2]
        amnt = lines[i + 3]
        line = lines[i + 4]
        i += 3
        ebal += float(amnt)
        [mm, dd, yycc] = datx.split('/')
        date = yycc + '-' + mm + '-' + dd
        # print('--DEPOSITS', i, startline, startline + i, date, desc, amnt, '[%s]' % line);  #sys.exit(0)
        if float(amnt) == 0: amnt = '0.00'
        actv = AccountActivity(self.bank, self.year, self.month, abal, ebal, self.tran, self.anum1, acctType, date, desc, amnt)
        abal = ebal
        self.accountActivity.append(actv)
        # showConts(acctType + ' Activity ' + str(self.tran), actv)
        self.tran += 1
        totalAmnt += float(amnt)

def getBalancesForChecking2(self, startline, acctType, noteId):
  nt = StatementNotes(self.bank, self.year, self.month, noteId, 'Money Market Savings ' + self.anum2, None)
  self.statementNotes.append(nt)
  showConts(acctType + ' Statement Notes %d'%noteId, nt)
  ebal = 0.0
  lines = self.lines[startline:]
  for i, line in enumerate(lines):
    if re.search('^Beginning balance on', line):
      self.abal2 = cleanMoney(lines[i + 1])
      ebal = float(self.abal2)
    elif re.search('^Ending balance on', line):
      self.ebal2 = float(cleanMoney(lines[i + 1]))
      if round(ebal, 2) == round(self.ebal2, 2):
        tran = 0
        actv = AccountActivity(self.bank, self.year, self.month, self.abal2, self.ebal2, tran, self.anum2, acctType, None, 'No Activities in this Account', None)
        showConts('Checking2 activity 0', actv)
        self.accountActivity.append(actv)
      else:
        print('AssertionError[%f]<>[%f], there must be some trans, should add the activities accordingly'%(ebal, self.ebal2))
      # print('abal2[%s], ebal2[%s]' % (self.abal2, self.ebal2))
        sys.exit(0)
      return
    elif 'Deposits and other additions' == line:
      note = line
      amnt = float(cleanMoney(lines[i + 1]))
      ebal += amnt
      if amnt != 0:
        noteId += 1
        nt = StatementNotes(self.bank, self.year, self.month, noteId, note, amnt)
        self.statementNotes.append(nt)
        showConts(acctType + ' Statement Notes %d'%noteId, nt)
    elif 'Withdrawals and other subtractions' == line:
      note = line
      amnt = float(cleanMoney(lines[i + 1]))
      ebal += amnt
      if amnt != 0:
        noteId += 1
        nt = StatementNotes(self.bank, self.year, self.month, noteId, note, amnt)
        self.statementNotes.append(nt)
        showConts(acctType + ' Statement Notes %d'%noteId, nt)
    elif line == 'Service fees':
      amnt = float(cleanMoney(lines[i + 1]))
      ebal += amnt
      if amnt != 0:
        noteId += 1
        nt = StatementNotes(self.bank, self.year, self.month, noteId, 'Service fees', amnt)
        self.statementNotes.append(nt)
        showConts(acctType + ' Statement Notes %d'%noteId, nt)