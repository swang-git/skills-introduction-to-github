#!/bin/python

from bs4 import BeautifulSoup
from datetime import datetime
import re, sys
from urllib import request
from Utils import showDict, showConts, mval
from Bank import Statement, TotalAssets
from pathlib import Path

class AnnuityAccount(Statement):
  def __getattr__(self, key): return None
  def __init__(self, bank, yearMonth, filename):
    super().__init__(bank, yearMonth, filename)
    self.holding = []
    self.notes = []
    self.acctName = 'Retirement Annuity'
    self.symb = 'FMPCC'

  def getData(self):
    # self.filepath() can't call it here why?
    self.setTxtUsingFitz()
    # for line in self.lines: print('-dg-Annuity XXXX', line)
    lines = self.lines
    for i, line in enumerate(lines):
      if 'Contract Number:' in line:
        _, anum = line.split(':')
        self.acctNum = anum.strip()
        # print('acctName[%s], acctNum[%s]'%(anam, anum))
      elif line == 'Since Inception':
        i += 1; sp1 = lines[i]
        i += 4; sp2 = lines[i]
        i += 3; sp3 = lines[i]
        i += 3; sp4 = lines[i]
        adict = {'Since Inception': [sp1, sp2, sp3, sp4]}
        self.balE = mval(sp4)
        cost = mval(sp2)
        # print(adict)
      elif line == 'This Period':
        i += 1; sp1 = lines[i]
        i += 3; sp2 = lines[i]
        i += 3; sp3 = lines[i]
        i += 3; sp4 = lines[i]
        self.balB = mval(sp2)
        adate, _, edate = sp1.split(' ')
        adict = {'This Period': [sp1, sp2, sp3, sp4]}
        # print(adict)
      elif 'End of Period' in line:
        i += 8; sp1 = lines[i]
        i += 1; sp2 = mval(lines[i])
        i += 1; sp3 = mval(lines[i])
        i += 1; sp4 = mval(lines[i])
        i += 1; sp5 = mval(lines[i])
        i += 1; sp6 = mval(lines[i])
        i += 1; sp7 = mval(lines[i])
        adict = {'Investment Options Summary': [sp1, sp2, sp3, sp4, sp5, sp6, sp7]}
        # print(adict); sys.exit(0)
        quan = sp5
        pric = sp6
        assert round(sp2 * sp3 - sp4, 1) == 0
        assert round(sp5 * sp6 - sp7, 1) == 0
        break
    self.dateB = str(datetime.strptime(adate, '%m/%d/%Y'))[:10]
    self.dateE = str(datetime.strptime(edate, '%m/%d/%Y'))[:10]
    # print('abal[%s], ebal[%s], diff[%8.2f], adate[%s], edate[%s]'%(self.balB, self.balE, self.balE-self.balB, self.dateB, self.dateE))
    # self.accountAssets = TotalAssets(self.bank, self.year, self.month, self.balB, self.balE, 0, self.dateB, self.dateE, 'Contract#:232080445')
    self.holdings = [[self.acctName, self.acctNum, quan, pric, self.balB, self.balE, cost, self.symb]]
    # showConts('Annuity Assets', self.accountAssets)
    # print(' XXXX Annuity, balB[%s], balE[%s]'%(self.balB, self.balE))
    print('\n\tself.filepath:[%s]' % self.filepath)

    return
