#!/bin/python

from bs4 import BeautifulSoup
from datetime import datetime
import re, sys, calendar
from urllib import request
from Utils import mval, showTag, showConts, showDict
from Bank import Statement
from FidelityFunctions import changesInPortfolioValue, getAccountAssets, incomeSummary, accountDetails, checkAccountBalances
from FidelityFunctions import stockHoldings, mutualFundHoldings, coreHoldings, getDeposits, getOtherWithdrawals
from FidelityFunctions import getInvestmentActivity, getBillPaymentActivity, getDailyAddtinsAndSubtractions, getAllTablesAndHondingIndex

class RothAndIndividualAccounts(Statement):
  def __getattr__(self, key): return None
  def __init__(self, bank, yearMonth, filename):
    super().__init__(bank, yearMonth, filename)
    _, num_days = calendar.monthrange(int(self.year), int(self.month))
    self.dateB = '-'.join([self.year, self.month, '1'])
    self.dateE = '-'.join([self.year, self.month, str(num_days)])
    self.accountActivity = []
    self.holdings = []
    self.mutualFundHoldings = []
    self.coreHoldings = []

  def getData(self):
    # self.setFilepath()
    soup = self.getSoup()
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

    # acIdx = 1
    # hcnt = len(self.holdings)
    # self.holdings += stockHoldings(self, soup, acIdx)
    # self.holdings += mutualFundHoldings(self, soup, acIdx)
    # self.holdings += coreHoldings(self, soup, acIdx)
    # checkAccountBalances(self, acIdx, hcnt)
    # return

    # changesInPortfolioValue(self, soup, balance)
    # getAccountAssets(self, soup, mval(balance))
    # incomeSummary(self, soup)
    # # need to get num of trades tabel summary="Commission Level" -- do it later
    # accountDetails(self, soup)

    # shold = stockHoldings(self, soup, 0)
    # if shold: self.stockHoldings.append(shold)
    # shold = stockHoldings(self, soup, 1)
    # if shold: self.stockHoldings.append(shold)

    # mhold = mutualFundHoldings(self, soup, 0)
    # if mhold: self.mutualFundHoldings.append(mhold)
    # mhold = mutualFundHoldings(self, soup, 1)
    # if mhold: self.mutualFundHoldings.append(mhold)

    # hold = coreHoldings(self, soup, 0)
    # if hold: self.coreHoldings.append(hold)
    # hold = coreHoldings(self, soup, 1)
    # if hold: self.coreHoldings.append(hold)

    for acIdx in [0, 1]:
      atv = getInvestmentActivity(self, soup, acIdx)
      self.accountActivity += atv
      atv = getBillPaymentActivity(self, soup, acIdx)
      if atv: self.accountActivity += atv
      atv = getDeposits(self, soup, acIdx)
      if atv: self.accountActivity += atv
      atv = getOtherWithdrawals(self, soup, acIdx)
      if atv: self.accountActivity += atv
      getDailyAddtinsAndSubtractions(self, soup, acIdx)

    # atv = InvestmentActivity(self, soup, 1)
    # self.accountActivity.append(atv)
    # dailyAddtinsAndSubtractions(self, soup, 1)

    # for ast in self.accountAssets: showConts('Account Assets', ast)

    # print('\n\t\t\t%s ~~ %s'%(self.dateB, self.dateE))
    # self.showAccountActivity()
    # self.showStockHoldings()
    # self.showMutualFundHoldings()
    # self.showCoreHoldings()

    return

  def showAccountActivity(self):
    for i, actvs in enumerate(self.accountActivity):
      ast = self.accountAssets[i]
      showTag('Activity for Account %s' % ast.acctName + ' - ' + ast.acctNum)
      for x in actvs: print(x)
      print()
  def showStockHoldings(self):
    for i, holdings in enumerate(self.stockHoldings):
      ast = self.accountAssets[i]
      showTag('Stock Holdings for Account %s' % ast.acctName + ' - ' + ast.acctNum)
      for hold in holdings: showDict(None, hold)
      print()
  def showMutualFundHoldings(self):
    for i, holdings in enumerate(self.mutualFundHoldings):
      ast = self.accountAssets[i]
      showTag('Mutual Fund Holdings for Account %s' % ast.acctName + ' - ' + ast.acctNum)
      for hold in holdings: showDict(None, hold)
      print()
  def showCoreHoldings(self):
    for i, holdings in enumerate(self.coreHoldings):
      ast = self.accountAssets[i]
      showTag('Core Holdings for Account %s' % ast.acctName + ' - ' + ast.acctNum)
      for x in holdings: showDict(None, x)
      print()

  def getSoup(self):
    # filepath = '/sites/webdata/docs/' + self.bank + '/' + self.yearMonth' + '_IRA.html'
    url = 'file://' + self.filepath
    soup = BeautifulSoup(request.urlopen(url), 'html.parser')
    return soup
