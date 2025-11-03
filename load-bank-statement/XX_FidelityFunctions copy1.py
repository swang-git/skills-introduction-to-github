from Utils import cleanMoney, mval, isFloat, showDictO, mval, showConts, gtxt, padsp, getListFromTds, showTag, getDictValByPartialKey
from Utils import showConts, showDict, showDictO, gval, gpad, gdat, gsym, xmap
import sys
from functools import partial
d2o=partial(type, "d2o", ())
DEBUG=None

def getAllTableH4s(self):
  return self.soup.find_all(re.compile(r'(table|h4'))

# process section: "Changes in Portfolio Value"
def changesInPortfolioValue(self, soup, balance):
  cipv = soup.find('table', {'summary': "Changes in Portfolio Value"})
  # print(cipv.prettify())
  tds = cipv.find_all('td')
  changesInPV = dict()
  for i, td in enumerate(tds):
    ki = i * 2
    vi = ki + 1
    try:
      changesInPV.update({tds[ki].text.strip(): tds[vi].text.strip()})
    except: pass
  showDictO('Changes in Portfolio Value', changesInPV)
  enval = changesInPV.get('Ending Net Value **')
  try: assert balance == enval
  except:
    print('balance[%s] == Ending Net Value **[%s]' % (balance, enval))
    sys.exit(0)
  return

# process section: <table summary="Value by Account />"
def valueByAccount(self, soup, balance):
  vba = soup.find('table', {'summary': 'Value by Account'}).findAll('tr')[1:]
  # print(vba)
  vbaList = []
  for tr in vba:
    tds = tr.findAll('td')
    v = ValueByAccount(
      tds[0].text.strip(),
      tds[2].text.strip(),
      mval(tds[3].text.strip()),
      mval(tds[4].text.strip())
    )
    vbaList.append(v)
  self.accountAssets = vbaList
  for v in vbaList: showConts('Value by Account = Account Assets for each Account', v)

  # print('%s %s'%(vbaList[0].ebal, vbaList[1].ebal))
  cval = round(vbaList[0].ebal + vbaList[1].ebal, 2)
  try: assert balance == cval
  except:
    print('AssertionError:balance[%s]==[%s]' % (balance, cval))
    sys.exit(0)
  return

# process section: <table summary="Income Summary">
def incomeSummary(self, soup):
  incs = soup.find('table', {'summary': "Income Summary"}).find('tbody')
  # print(incs.prettify())
  incsList = []
  tds = incs.findAll('td')
  for i, td in enumerate(tds):
    if tds[i].text.strip('\n') == '': continue
    k0= i * 4
    k1 = k0 + 1
    k2 = k0 + 2
    try:
      # incsList.append({ tds[k0].text.strip(): [ gtxt(tds[k1]), gtxt(tds[k2]) ] })
      incsList.append({ gtxt(tds[k0]): [ gval(tds[k1]), gval(tds[k2]) ] })
    except: pass
  showTag('Income Summary')
  for inc in incsList: showDict(None, inc)
  acsm = soup.find('table', {'summary': "Account Summary"})
  return

# process section: "Account Detals"
def accountDetails(self, soup):
  ac = soup.find('div', {'id': "acdetailscontents"})
  # print(ac.prettify())
  spns = ac.find('h3').findAll('span')
  acctName = spns[0].text.strip()
  acctbal = spns[1].text.strip()
  print('[%s], [%s]' % (acctName, acctbal))

  acsm = ac.find('table', {'summary': "Account Summary"})
  acDetailsList = getListFromTds(acsm)
  # print(padsp('-o^o- Account Details -o^o-', 80))
  showTag('Account Details')
  for ad in acDetailsList: showDictO(None, ad)
  assert acctbal == getDictValByPartialKey(acDetailsList, 'Ending Value as of')
  return

# process section: "summary=Stocks" Holdings as of
def stockHoldings(self, soup, acIdx):
  hx = soup.findAll('table', {'summary': "Stocks"})
  # print('XXXXXX', type(hx), len(ho))
  # ho = soup.findAll('table', {'summary': "Stocks"})[acIdx].find('tbody')
  if acIdx >= len(hx): return None
  holdingList = []
  ho = hx[acIdx].find('tbody')
  # print(ho.prettify())
  tds = ho.findAll('td')
  for i, td in enumerate(tds):
    k0 = i * 7
    k1 = k0 + 1
    k2 = k0 + 2
    k3 = k0 + 3
    k4 = k0 + 4
    k5 = k0 + 5
    k6 = k0 + 6
    try:
      holdingList.append({ gtxt(tds[k0]): [ gval(tds[k2]), gval(tds[k3]), gval(tds[k4]), gval(tds[k5]), gval(tds[k6]) ] })
    except:
      pass
  ast = self.accountAssets[acIdx]
  showTag('Stock Holdings for Account %s'%ast.acctName + ' - ' + ast.acctNum)
  for x in holdingList: showDict(None, x)
  return holdingList

def mutualFundHoldings(self, soup, acIdx):
  hx = soup.findAll('table', {'summary': "Mutual Funds"})
  if acIdx >= len(hx): return None
  ho = hx[acIdx].find('tbody')
  holdingList = []
  tds = ho.findAll('td')
  for i, td in enumerate(tds):
    k0 = i * 7
    k1 = k0 + 1
    k2 = k0 + 2
    k3 = k0 + 3
    k4 = k0 + 4
    k5 = k0 + 5
    k6 = k0 + 6
    try:
      holdingList.append({ gtxt(tds[k0]): [ gval(tds[k2]), gval(tds[k3]), gval(tds[k4]), gval(tds[k5]), gval(tds[k6]) ] })
    except:
      pass
  ast = self.accountAssets[acIdx]
  showTag('Mutual Fund Holdings for Account %s'%ast.acctName + ' - ' + ast.acctNum)
  for x in holdingList: showDict(None, x)
  return holdingList

def coreHoldings(self, soup, acIdx):
  hx = soup.findAll('table', attrs={'summary': "Core Account"}, class_="holdings grid")
  # hx = soup.findAll('table', {'summary': "Core Account"})
  # print(hx.prettify())
  if acIdx >= len(hx): return None
  ho = hx[acIdx].find('tbody')
  tds = ho.findAll('td')
  holdingList = []
  for i, td in enumerate(tds):
    k0 = i * 7
    k1 = k0 + 1
    k2 = k0 + 2
    k3 = k0 + 3
    k4 = k0 + 4
    k5 = k0 + 5
    k6 = k0 + 6
    try:
      holdingList.append({ gtxt(tds[k0]): [ gval(tds[k2]), gval(tds[k3]), gval(tds[k4]), gval(tds[k5]), gval(tds[k6]) ] })
    except:
      pass
  ast = self.accountAssets[acIdx]
  showTag('Core Holdings for Account %s'%ast.acctName + ' - ' + ast.acctNum)
  for x in holdingList: showDict(None, x)
  return holdingList

# process section: "Investment Activity"
def InvestmentActivity(self, soup, acIdx):
  actvList = []
  ia = soup.findAll('table', {'summary': "Investment Activity"})[acIdx].find('tbody')
  # print(ia.prettify())
  tds = ia.find_all('td')
  # tds = [for x in trs: x.find_all('td')]
  # print('tds length[%s]' % len(tds))
  for i, td in enumerate(tds):
    k0 = i * 7
    k1 = k0 + 1
    k2 = k0 + 2
    k3 = k0 + 3
    k4 = k0 + 4
    k5 = k0 + 5
    k6 = k0 + 6
    try:
      actv = [gdat(str(self.year), tds[k0]), xmap(self, tds[k1], acIdx), gtxt(tds[k2]), gval(tds[k3]), gval(tds[k4]), gval(tds[k5]), gval(tds[k6])]
      actvList.append(actv)
    except:
      pass
  if acIdx == 1:
    depo= deposits(self, soup, acIdx)
    if depo: actvList += depo
    actvList += otherWithdrawals(self, soup, acIdx)
  ast = self.accountAssets[acIdx]
  showTag('Investment Activity for Account %s'%ast.acctName + ' - ' + ast.acctNum)
  for actv in actvList: print(actv)
  return actvList

def deposits(self, soup, acIdx):
  depList = []
  depx = soup.find('table', attrs={'summary': "Deposit_Detail"})
  if depx == None: return None
  depo = depx.find('tbody')
  # print(depo.prettify())
  tds = depo.find_all('td')
  for i, td in enumerate(tds):
    k0 = i * 7
    k1 = k0 + 1
    k2 = k0 + 2
    k3 = k0 + 3
    k4 = k0 + 4
    k5 = k0 + 5
    k6 = k0 + 6
    try:
      # print('   XXXX Total [%s]'%tds[k0].text.strip())
      if 'Total' in tds[k0].text.strip() or tds[k0].text.strip() == '': break
      actv = [gdat(str(self.year), tds[k0]), 'Deposit', gval(tds[k2])]
      depList.append(actv)
    except:
      pass
  ast = self.accountAssets[acIdx]
  showTag('Deposits for Account %s'%ast.acctName + ' - ' + ast.acctNum)
  for actv in depList: print(actv)
  return depList

def otherWithdrawals(self, soup, acIdx):
  wdList = []
  wd = soup.find('table', attrs={'summary': "Other Withdrawals"}).find('tbody')
  tds = wd.find_all('td')
  for i, td in enumerate(tds):
    k0 = i * 7
    k1 = k0 + 1
    k2 = k0 + 2
    k3 = k0 + 3
    k4 = k0 + 4
    k5 = k0 + 5
    k6 = k0 + 6
    try:
      # print('   XXXX Total [%s]'%tds[k0].text.strip())
      if 'Total' in tds[k0].text.strip() or tds[k0].text.strip() == '': break
      actv = [gdat(str(self.year), tds[k0]), 'Withdrawals', gtxt(tds[k1]), gtxt(tds[k2]), gval(tds[k3])]
      wdList.append(actv)
    except:
      pass
  ast = self.accountAssets[acIdx]
  showTag('Other Withdrawals for Account %s'%ast.acctName + ' - ' + ast.acctNum)
  for actv in wdList: print(actv)
  return wdList

def billPaymentActivity(self, soup):
  bpx = soup.find('table', {'summary': "Bill Payment Activity"})
  if bpx == None: return None
  bp = bpx.find('tbody')
  actvList = []
  tds = bp.find_all('td')
  # print('tds length[%s]' % len(tds))
  for i, td in enumerate(tds):
    k0 = i * 7
    k1 = k0 + 1
    k2 = k0 + 2
    k3 = k0 + 3
    k4 = k0 + 4
    k5 = k0 + 5
    k6 = k0 + 6
    try:
      actv = [gdat(str(self.year), tds[k0]), gtxt(tds[k1]), gtxt(tds[k2]), gtxt(tds[k3]), gval(tds[k4]), gval(tds[k5]), gval(tds[k6])]
      actvList.append(actv)
    except:
      pass
  showTag('Bill Payment Activity for Account: 0')
  for actv in actvList: print(actv)
  return

def dailyAddtinsAndSubtractions(self, soup, acIdx):
  bp = soup.findAll('table', {'summary': "Daily Additions and Subtractions"})[acIdx].find('tbody')
  # print(bp.prettify())
  tds = bp.find_all('td')
  # print('tds length[%s]' % len(tds))
  actvList = []
  for i, td in enumerate(tds):
    k0 = i * 8
    k1 = k0 + 1
    k2 = k0 + 2
    k3 = k0 + 3
    k4 = k0 + 4
    k5 = k0 + 5
    k6 = k0 + 6
    k7 = k0 + 7
    try:
      actv = [
        gdat(str(self.year),
        tds[k0]), gtxt(tds[k1]),
        gtxt(tds[k2]),
        gsym(self, tds[k3]),
        gval(tds[k4]),
        gval(tds[k5]),
        gval(tds[k6]),
        gval(tds[k7])
      ]
      actvList.append(actv)
    except:
      pass
  showTag('Daily Adds & Subs for Account: %s'%acIdx)
  for actv in actvList: print(actv)
  return

class XXCipv():
  def __getattr__(self, key): return None
  def __init__(self, abal, additions, withdrawals, fTaxWithHeld, sTaxWithhold, investValChgs, ebal, eNetVal):
    self.abal = abal
    self.additions = additions
    self.withdrawals = withdrawals
    self.fTaxWithhold = fTaxWithhold
    self.sTaxWithhold = sTaxWithhold
    self.investValChgs = investValChgs
    self.ebal = ebal
    self.eNetVal = eNetVal

class ValueByAccount():
  def __getattr__(self, key): return None
  def __init__(self, acctName, acctNum, abal, ebal):
    self.acctName = acctName
    self.acctNum = acctNum
    self.abal = abal
    self.ebal = ebal