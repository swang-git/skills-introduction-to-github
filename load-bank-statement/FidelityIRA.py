#!/bin/python

from bs4 import BeautifulSoup
# from datetime import datetime
import re, sys, calendar
from urllib import request
from Utils import mval, showTag, showConts, showDict
from Bank import Statement
from FidelityFunctions import changesInPortfolioValue, getAccountAssets, incomeSummary, accountDetails
from FidelityFunctions import stockHoldings, mutualFundHoldings, coreHoldings, getDeposits, getOtherWithdrawals, checkAccountBalances
from FidelityFunctions import getInvestmentActivity, getBillPaymentActivity, getDailyAddtinsAndSubtractions, getAllTablesAndHondingIndex

class IRAAndIndividualAccounts(Statement):
  def __getattr__(self, key): return None
  def __init__(self, bank, yearMonth, filename):
    super().__init__(bank, yearMonth, filename)
    _, num_days = calendar.monthrange(int(self.year), int(self.month))
    self.dateB = '-'.join([self.year, self.month, '1'])
    self.dateE = '-'.join([self.year, self.month, str(num_days)])
    self.accountActivity = []
    self.holdingIndex = []
    self.allTables = []
    self.holdings = []
    self.stockHoldings = []
    self.mutualFundHoldings = []
    self.coreHoldings = []

  def getData(self):
    self.setFilepath()
    soup = self.getSoup()
    # print(soup.prettify()); sys.exit()
    getAllTablesAndHondingIndex(self, soup)
    balance = soup.find('span', {'class': "balance"}).text
    # print('balance[%s]' % balance)

    getAccountAssets(self, soup, mval(balance))

    hcnt = 0
    for acIdx in [0, 1]:
      if acIdx == 1: hcnt = len(self.holdings)
      self.holdings += stockHoldings(self, soup, acIdx)
      self.holdings += mutualFundHoldings(self, soup, acIdx)
      self.holdings += coreHoldings(self, soup, acIdx)
      checkAccountBalances(self, acIdx, hcnt)


    # changesInPortfolioValue(self, soup, balance)
    # valueByAccount(self, soup, mval(balance))
    # incomeSummary(self, soup)
    # # need to get num of trades table summary="Commission Level" -- do it later
    # accountDetails(self, soup)

    for acIdx in [0, 1]:
      atv = getInvestmentActivity(self, soup, acIdx)
      self.accountActivity += atv
      atv = getBillPaymentActivity(self, soup, acIdx)
      # print('XX atv', atv)
      if atv: self.accountActivity += atv
      atv = getDeposits(self, soup, acIdx)
      if atv: self.accountActivity += atv
      atv = getOtherWithdrawals(self, soup, acIdx)
      if atv: self.accountActivity += atv
      getDailyAddtinsAndSubtractions(self, soup, acIdx)


    # for ast in self.accountAssets: showConts('Account Assets', ast)

    # print('\n\t\t\t%s ~~ %s'%(self.dateB, self.dateE))
    # self.showAccountActivity()
    # self.showHoldings()
    # self.showMutualFundHoldings()
    # self.showCoreHoldings()

    return

  def XXXgetHoldings(self):
    acIdx = 0
    self.holdings += stockHoldings(self, soup, acIdx)
    self.holdings += mutualFundHoldings(self, soup, acIdx)
    self.holdings += coreHoldings(self, soup, acIdx)
    checkAccountBalances(self, acIdx, 0)
    hcnt = len(self.holdings)

    acIdx = 1
    self.holdings += stockHoldings(self, soup, acIdx)
    self.holdings += mutualFundHoldings(self, soup, acIdx)
    self.holdings += coreHoldings(self, soup, acIdx)
    checkAccountBalances(self, acIdx, hcnt)

  def showAccountActivity(self):
    for i, actvs in enumerate(self.accountActivity):
      ast = self.accountAssets[i]
      showTag('Activity for Account %s' % ast.acctName + ' - ' + ast.acctNum)
      for x in actvs: print(x)
      print()

  def showHoldings(self):
    for i, holdings in enumerate(self.holdings):
      ast = self.accountAssets[i]
      showTag('Holdings for Account %s' % ast.acctName + ' - ' + ast.acctNum)
      print('   XXX holdings[%s]'%holdings)
      print()

  def getSoup(self):
    url = 'file://' + self.filepath
    soup = BeautifulSoup(request.urlopen(url), 'html.parser')
    return soup
