import sys, re, calendar
from datetime import datetime
from Utils import padsp, isFloat, showConts
from Bank import Statement, StatementNotes, TotalAssets, Holding, bym
from FidelityIRA import IRAAndIndividualAccounts
from FidelityRoth import RothAndIndividualAccounts
from FidelityAnnuity import AnnuityAccount

class FidelityStatements(Statement):
  def __getattr__(self, key): return None
  def __init__(self, bank, yearMonth):
    super().__init__(bank, yearMonth, None)
    self.bank = bank
    self.yearMonth = yearMonth
    self.irastmt = IRAAndIndividualAccounts(bank, yearMonth, '_ira.html')
    self.rthstmt = RothAndIndividualAccounts(bank, yearMonth, '_roth.html')
    self.annstmt = AnnuityAccount(bank, yearMonth, '_annuity.pdf')
    self.accountActivity
    self.holdings = []
    self.allNotes

  def getData(self):
    _, ndays = calendar.monthrange(int(self.year), int(self.month))
    self.dateB = '-'.join([self.year, self.month, '01'])
    self.dateE = '-'.join([self.year, self.month, str(ndays)])
    print('dateB[%s], dateE[%s]'%(self.dateB, self.dateE))

    self.bym = bym(self.bank, self.year, self.month)

    self.irastmt.getData()
    self.rthstmt.getData()
    self.annstmt.getData()

    self.getTotalAssets()   ## do this 1st

    self.holdings += self.irastmt.holdings ## do these 2nd
    self.holdings += self.rthstmt.holdings
    self.holdings += self.annstmt.holdings

    # print('self.filepath:[%s]' % self.filepath)

    self.getAccountActivities()

    return

  def getAnnuityHoldings(self):
    hold = self.annstmt.holding
    key, val = hold.popitem()
    # print(key, val); sys.exit(0)
    hold = Holding(self.bym, self.annstmt.acctNum, self.annstmt.acctName, key, val)
    self.holdings.append(hold)
    return hold

  def getStockHoldings(self):
    astx = self.irastmt.accountAssets
    for i, ast in enumerate(astx):
      if i >= len(self.irastmt.stockHoldings):
        print('No stock holdings in this account %s(%s) no processing, skippping...' % (ast.acctName, ast.acctNum))
        return
      for items in self.irastmt.stockHoldings[i]:
        try: item = items.popitem()
        except: break
        key, val = item
        hold = Holding(self.bym, ast.acctNum, ast.acctName, key, val)
        self.allHoldings.append(hold)

    astx = self.rthstmt.accountAssets
    for i, ast in enumerate(astx):
      if i >= len(self.rthstmt.stockHoldings):
        print('No stock holdings in this account %s(%s) no processing, skippping...' % (ast.acctName, ast.acctNum))
        return
      for items in self.rthstmt.stockHoldings[i]:
        try: item = items.popitem()
        except: break
        key, val = item
        hold = Holding(self.bym, ast.acctNum, ast.acctName, key, val)
        self.allHoldings.append(hold)

  def getMutualFundHoldings(self):
    astx = self.irastmt.accountAssets
    for i, ast in enumerate(astx):
      for items in self.irastmt.mutualFundHoldings[i]:
        try: item = items.popitem()
        except: break
        key, val = item
        hold = Holding(self.bym, ast.acctNum, ast.acctName, key, val)
        self.allHoldings.append(hold)

    astx = self.rthstmt.accountAssets
    for i, ast in enumerate(astx):
      for items in self.rthstmt.mutualFundHoldings[i]:
        try: item = items.popitem()
        except: break
        key, val = item
        hold = Holding(self.bym, ast.acctNum, ast.acctName, key, val)
        self.allHoldings.append(hold)

  def getCoreHoldings(self):
    astx = self.irastmt.accountAssets
    for i, ast in enumerate(astx):
      if i >= len(self.rthstmt.stockHoldings):
        print('No core holdings in this account %s(%s) no processing, skippping...' % (ast.acctName, ast.acctNum))
        return
      for items in self.irastmt.coreHoldings[i]:
        if not bool(items): continue # empty dict -- meaning no core holdings
        # print(' XXXX type of core holdings[%s]'%bool(items), items.popitem()) #; sys.exit(0)
        try:
          key, val = items.popitem()
          hold = Holding(self.bym, ast.acctNum, ast.acctName, key, val)
          self.allHoldings.append(hold)
        except: pass

    astx = self.rthstmt.accountAssets
    for i, ast in enumerate(astx):
      # print(ast.acctName, ast.acctNum, self.rthstmt.coreHoldings[1])
      for items in self.rthstmt.coreHoldings[i]:
        if not bool(items):
          print('--NOTES-- empty dict for core holdings', items)
          continue # a empty dict -- meaning no core holdings
        item = items.popitem()
        key, val = item
        hold = Holding(self.bym, ast.acctNum, ast.acctName, key, val)
        # print('   XXXXX', hold)
        self.allHoldings.append(hold)
    return

  def getAccountActivities(self):
    actv0 = self.irastmt.accountActivity
    # for i, a in enumerate(actv0): print('%s %s'%(padsp(i, 2), a))
    actv1 = self.rthstmt.accountActivity
    # for i, a in enumerate(actv1): print('%s %s'%(padsp(i, 2), a))
    # for i, a in enumerate(actv0 + actv1): print('%s %s'%(padsp(i, 2), a))
    self.accountActivity = actv0 + actv1


  def getTotalAssets(self):
    iast = self.irastmt.accountAssets
    rast = self.rthstmt.accountAssets
    anst = self.annstmt
    # print(' XXXX Fstatmt, balB[%s], balE[%s]'%(anst.balB, anst.balE))
    shortAcctName = iast[0].acctName[0:10]
    # shortAcctName = 'Indv'
    self.primaryAcct = shortAcctName + ' ' + iast[0].acctNum
    # abalT = iast[0].abal + iast[1].abal + rast[0].abal + rast[1].abal + self.annstmt.balB
    # ebalT = iast[0].ebal + iast[1].ebal + rast[0].ebal + rast[1].ebal + self.annstmt.balE
    # abalT = round(iast[0].abal + iast[1].abal + rast[0].abal + rast[1].abal + self.annstmt.balB + anst.balB, 2)
    # ebalT = round(iast[0].ebal + iast[1].ebal + rast[0].ebal + rast[1].ebal + self.annstmt.balE + anst.balE, 2)
    abalT = round(iast[0].abal + iast[1].abal + rast[0].abal + rast[1].abal + anst.balB, 2)
    ebalT = round(iast[0].ebal + iast[1].ebal + rast[0].ebal + rast[1].ebal + anst.balE, 2)
    # ebalT = rast[0].ebal + rast[1].ebal
    self.totalAssets = TotalAssets(self.bank, self.year, self.month, abalT, ebalT, 0, self.dateB, self.dateE, self.primaryAcct)
    # showConts('Fidelity Total Assets', self.totalAssets)