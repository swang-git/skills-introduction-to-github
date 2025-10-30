from Utils import cleanMoney, mval, isFloat, showDictO, mval, showConts, gtxt, padsp, getListFromTds, showTag, getDictValByPartialKey
from Utils import showConts, showDict, showDictO, gval, gpad, gdat, gsym, xmap, showList
import sys, re
from functools import partial
d2o=partial(type, "d2o", ())
DEBUG=1
def dg(str):
  if DEBUG: print(str)
def checkAccountBalances(self, acIdx): ## for start balance calc need to add SOLD parts in Other Withdrawals -- no check it for now
  dg('\t -dg- checkAccountBalances():')
  abal = 0
  ebal = 0
  for i, hold in enumerate(self.holdings):
    dg('-dg-holdings:%d, %d, %s'%(acIdx, i, hold))
    # dg('sym[%s], dat%s'%(sym, dat))
    if dat[2]: abal += dat[4]
    ebal += dat[5]
  # dg('abal[%.2f], ebal[%.2f]' % (abal, ebal))
  ast = self.accountAssets[acIdx]
  try: assert round(abal - ast.abal, 2) == 0 and round(ebal - ast.ebal, 2) == 0
  except:
    print('\n\t checkAccountBalances() FAILED:')
    print('\t ast.abal == abal and ast.ebal == ebal')
    print('\t start balance check: round(%.2f - %.2f, 2) and end balance check: round(%.2f - %.2f, 2)' % (ast.abal, abal, ast.ebal, ebal))

def checkAccountBalances(self, acIdx, hcnt):
  abal = 0.0
  ebal = 0.0
  for i, h in enumerate(self.holdings[hcnt:]):
    # dg('-dg-holdings: ac%d, %s %s'%(acIdx, padsp(str(i+1), 2), h))
    if h[4]: abal += h[4]
    ebal += h[5]
  ast = self.accountAssets[acIdx]
  dg('-dg- checkAccountBalances() account %s, calc start_balance - end_balance: %.2f - %.2f'%(ast.acctNum, abal, ebal))
  dg('-dg- checkAccountBalances() account %s, stmt start_balance - end_balance: %.2f - %.2f'%(ast.acctNum, ast.abal, ast.ebal))
  try:
    assert abs(round(abal - ast.abal, 2)) == 0
    # assert abs(round(abal - ast.abal, 2)) == 2000
  except:
    print('\n\t checkAccountBalances() FAILED for start_balance comparing:')
    print('\t FAILED_tag: sum(start_balance) of holdings: abal<>ast.abal :start_balance on statement')
    print('\t FAILED_tag: start balances check for account %s: %.2f - %.2f = %.2f' % (ast.acctNum, abal, ast.abal, abal - ast.abal))
    # sys.exit(0)

  try: assert abs(round(ebal - ast.ebal, 2)) < 0.7
  except:
    print('\n\t checkAccountBalances() FAILED for end_balance comparing:')
    print('\t FAILED_tag: sum(end_balance) of holdings: ebal <> ast.ebal :end_balance on statement')
    print('\t FAILED_tag: end balances check for account %s: %.2f - %.2f = %.2f' % (ast.acctNum, ebal, ast.ebal, ebal - ast.ebal))
    sys.exit(0)

def getAllTablesAndHondingIndex(self, soup):
  self.tables = []
  # self.holdingStarts = []
  holding0 = None
  holding1 = None
  self.allTables = soup.find_all(['table', 'h4'])
  for i, tag in enumerate(self.allTables):
    if tag.find('strong'):
      txt = tag.find('strong').text.strip()
      # print('[%s] [%s]'%(i, txt))
      if txt == 'Holdings':
        # print(i, txt)
        if holding0: holding1 = i
        else: holding0 = i
  dg('holdings start at: %dth and %dth table/h4 tag' %(holding0, holding1))
  self.tables.append(self.allTables[holding0:holding1])
  self.tables.append(self.allTables[holding1:])

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
def getAccountAssets(self, soup, balance):
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
  # for v in vbaList: showConts('Value by Account = Account Assets for each Account', v)

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

def getSummaryTable(self, soup, acIdx, valstr):
  ptable = None
  for i, table in enumerate(self.tables[acIdx]):
    summary = table.get('summary')
    if summary == valstr and table.get("class") == ["holdings", "grid"]:
      # print('    XXX', table.get("class"))
      # dg('acIdx[%d], i[%d]: summary="%s"'%(acIdx, i, summary))
      # dg('[%s]'%table)
      ptable = table
      # return ptable
  if not ptable: print('acIdx[%d], i[%d]: No table for attr: summary="%s"'%(acIdx, i, valstr))
  return ptable

# process section: "summary=Stocks" Holdings as of
def stockHoldings(self, soup, acIdx):
  holdingList = []
  ast = self.accountAssets[acIdx]
  ptable = getSummaryTable(self, soup, acIdx, "Stocks")
  if not ptable: return []
  ho = ptable.find('tbody')
  tds = ho.findAll('td')
  # dg('[%s'%tds); sys.exit(0)
  for i, td in enumerate(tds):
    k0 = i * 7
    k1 = k0 + 1
    k2 = k0 + 2
    k3 = k0 + 3
    k4 = k0 + 4
    k5 = k0 + 5
    k6 = k0 + 6
    try:
      # holdingList.append({ gtxt(tds[k0]): [ ast.acctName, ast.acctNum, gval(tds[k2]), gval(tds[k3]), gval(tds[k4]), gval(tds[k5]), gval(tds[k6]) ] })
      holdingList.append([ ast.acctName, ast.acctNum, gval(tds[k2]), gval(tds[k3]), gval(tds[k4]), gval(tds[k5]), gval(tds[k6]), gtxt(tds[k0]) ])
    except:
      pass
  showTag('Stock Holdings for Account %s'%ast.acctName + ' - ' + ast.acctNum)
  for x in holdingList: print(x)
  return holdingList

def mutualFundHoldings(self, soup, acIdx):
  ast = self.accountAssets[acIdx]
  holdingList = []
  ptable = getSummaryTable(self, soup, acIdx, "Mutual Funds")
  if not ptable: return []
  ho = ptable.find('tbody')
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
      # holdingList.append({ gtxt(tds[k0]): [ ast.acctName, ast.acctNum, gval(tds[k2]), gval(tds[k3]), gval(tds[k4]), gval(tds[k5]), gval(tds[k6]) ] })
      holdingList.append([ ast.acctName, ast.acctNum, gval(tds[k2]), gval(tds[k3]), gval(tds[k4]), gval(tds[k5]), gval(tds[k6]), gtxt(tds[k0]) ])
    except:
      pass
  showTag('Mutual Fund Holdings for Account %s'%ast.acctName + ' - ' + ast.acctNum)
  for x in holdingList: print(x)
  return holdingList

def coreHoldings(self, soup, acIdx):
  ast = self.accountAssets[acIdx]
  holdingList = []
  ptable = getSummaryTable(self, soup, acIdx, "Core Account")
  # dg('\t-dg- Core Account table -- ptable[%s]'%ptable); sys.exit(0)
  if not ptable: return []
  ho = ptable.find('tbody')
  tds = ho.findAll('td')
  # dg('\t-dg- Core Account tds -- ptable[%s]'%[x.text.strip() for x in tds])
  for i, td in enumerate(tds):
    k0 = i * 7
    k1 = k0 + 1
    k2 = k0 + 2
    k3 = k0 + 3
    k4 = k0 + 4
    k5 = k0 + 5
    k6 = k0 + 6
    try:
      # holdingList.append({ gtxt(tds[k0]): [ ast.acctName, ast.acctNum, gval(tds[k2]), gval(tds[k3]), gval(tds[k4]), gval(tds[k5]), gval(tds[k6]) ] })
      holdingList.append([ ast.acctName, ast.acctNum, gval(tds[k2]), gval(tds[k3]), gval(tds[k4]), gval(tds[k5]), gval(tds[k6]), gtxt(tds[k0]) ])
    except:
      pass
  showTag('Core Holdings for Account %s'%ast.acctName + ' - ' + ast.acctNum)
  for x in holdingList: print(x)
  return holdingList

def XXXmutualFundHoldings(self, soup, acIdx):
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

def XXXcoreHoldings(self, soup, acIdx):
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
def getInvestmentActivity(self, soup, acIdx):
  idx = str(acIdx + 1)
  iax = soup.find('h3', id='ac' + idx).find_next('table', {'summary': "Investment Activity"})
  if iax == None: return None
  ia = iax.find('tbody')
  # print(ia.prettify())
  actvList = []
  tds = ia.find_all('td')
  # print(len(tds))
  for i, td in enumerate(tds):
    k0 = i * 7
    if k0 == len(tds): break
    if tds[k0].text.strip() == '': continue
    actv = [self.bank, self.year, self.month, re.sub('-', '', self.accountAssets[acIdx].acctName), self.accountAssets[acIdx].acctNum]
    actv.append(i + 1)
    actv.append(gdat(self.year, tds[k0]))
    actv.append(xmap(self, tds[k0 + 1], acIdx))
    actv.append(gtxt(tds[k0 + 2]))
    actv.append(gval(tds[k0 + 3]))
    actv.append(gval(tds[k0 + 4]))
    actv.append(gval(tds[k0 + 5]))
    actv.append(gval(tds[k0 + 6]))
    act7 = actv[7]
    act8 = actv[8]
    if 'TRANSFERRED' in act8:
      actv[7] = 'TransFr'
      actv[8] = act8 + ' ' + act7
    elif re.search('REDEEMED TO|PERSONAL WITHDRAWAL', act7):
      actv[7] = act7[0:5]
      actv[8] = act7[6:] + '~' + act8
    elif 'FDIC INSURED DEPOSIT' in act7:
      actv[7] = 'FDICid'
      actv[8] = act7 + '~' + act8

    actvList.append(actv)

  showList(self, 'Investment Activity', actvList, acIdx)
  return actvList

def getDeposits(self, soup, acIdx):
  hacx = soup.find('h3', id='ac2')
  if acIdx == 0: depx = hacx.find_previous('table', attrs={'summary': "Deposit_Detail"})
  if acIdx == 1: depx = hacx.find_next('table', attrs={'summary': "Deposit_Detail"})
  if depx == None: return None

  depList = []
  acNum = self.accountAssets[acIdx].acctNum
  acNam = self.accountAssets[acIdx].acctName
  depo = depx.find('tbody')
  # print(depo.prettify())
  tds = depo.find_all('td')
  print('tds:%d, acIdx:%d'%(len(tds), acIdx))
  for i, td in enumerate(tds):
    k0 = i * 3
    if tds[k0].text.strip() == '' or 'Total' in tds[k0].text or tds[k0].text.strip() == '': break
    actv = [self.bank, self.year, self.month, re.sub('-', '', self.accountAssets[acIdx].acctName), self.accountAssets[acIdx].acctNum]
    actv.append(i + 1)
    actv.append(gdat(self.year, tds[k0]))
    desc = gtxt(tds[k0 + 1])
    secu = 'Deposit'
    if 'CARDSVCC' in desc: secu = 'CardRv'
    actv.append(secu)
    actv.append(desc)
    actv.append(None)
    actv.append(None)
    amnt = gval(tds[k0 + 2])
    actv.append(amnt)
    actv.append(None)

    depList.append(actv)

  showList(self, 'Deposits', depList, acIdx)
  return depList

def getOtherWithdrawals(self, soup, acIdx):
  hacx = soup.find('h3', id='ac2')
  if acIdx == 0: wdx = hacx.find_previous('table', attrs={'summary': "Other Withdrawals"})
  if acIdx == 1: wdx = hacx.find_next    ('table', attrs={'summary': "Other Withdrawals"})
  if wdx == None: return None

  wd = wdx.find('tbody')
  tds = wd.find_all('td')
  wdList = []
  for i, td in enumerate(tds):
    k0 = i * 4
    if k0 >= len(tds) or 'Total' in tds[k0].text.strip() or tds[k0].text.strip() == '': break
    actv = [self.bank, self.year, self.month, re.sub('-', '', self.accountAssets[acIdx].acctName), self.accountAssets[acIdx].acctNum]
    actv.append(i + 1)
    actv.append(gdat(self.year, tds[k0]))
    secu = 'Withdrw'
    actv.append(secu)
    desc = gtxt(tds[k0 + 1]) + '~' + gtxt(tds[k0 + 2])
    actv.append(desc)
    amnt = gval(tds[k0 + 3])
    actv.append(None)
    actv.append(None)
    actv.append(amnt)
    actv.append(None)
    wdList.append(actv)

  showList(self, 'Other Withdrawals', wdList, acIdx)
  return wdList

def getBillPaymentActivity(self, soup, acIdx):
  hacx = soup.find('h3', id='ac2')
  if acIdx == 0: bpx = hacx.find_previous('table', {'summary':"Bill Payment Activity"})
  if acIdx == 1: bpx = hacx.find_next    ('table', {'summary':"Bill Payment Activity"})
  if bpx == None: return None

  bp = bpx.find('tbody')
  actvList = []
  tds = bp.find_all('td')
  # print('  ---- XXX --- tds:%d, acIdx:%d' % (len(tds), acIdx))
  for i, td in enumerate(tds):
    k0 = i * 7
    if k0 >= len(tds) - 3: break
    actv = [self.bank, self.year, self.month, re.sub('-', '', self.accountAssets[acIdx].acctName), self.accountAssets[acIdx].acctNum]
    actv.append(i + 1)
    actv.append(gdat(self.year, tds[k0]))
    # actv.append(gtxt(tds[k0 + 1]))
    actv.append('BillPay')
    address = re.sub('^[\*]{10}', '71 Shelley', gtxt(tds[k0 + 3]))
    address = re.sub('^[\*]{6}$', '71 Shelley Cir', address)
    actv.append(gtxt(tds[k0 + 2]) + '~' + re.sub('[\*]{10}', '71 Shelley', gtxt(tds[k0 + 3])))
    actv.append(gval(tds[k0 + 4]))
    actv.append(None)
    actv.append(gval(tds[k0 + 5]))
    actv.append(gval(tds[k0 + 6]))
    # print(actv)
    actvList.append(actv)
  showList(self, 'Bill Payment Activity', actvList, acIdx)
  return actvList

def getDailyAddtinsAndSubtractions(self, soup, acIdx):
  hacx = soup.find('h3', id='ac2')
  if acIdx == 0: dasx = hacx.find_previous('table', {'summary': "Daily Additions and Subtractions"})
  if acIdx == 1: dasx = hacx.find_next    ('table', {'summary': "Daily Additions and Subtractions"})
  if dasx == None: return None
  das = dasx.find('tbody')
  tds = das.find_all('td')
  # print(len(tds))
  nCols = 6 if len(tds) % 6 == 0 and len(tds) % 8 != 2 else 8

  dasList = []
  for i, td in enumerate(tds):
    k0 = i * nCols
    if k0 >= len(tds) - 2: break
    actv = []
    actv.append(gdat(self.year, tds[k0]))
    actv.append(gtxt(tds[k0 + 1]))
    actv.append(gtxt(tds[k0 + 2]))
    actv.append(gsym(self, tds[k0 + 3]))
    actv.append(gval(tds[k0 + 4]))
    actv.append(gval(tds[k0 + 5]))
    if nCols == 8:
      actv.append(gval(tds[k0 + 6]))
      actv.append(gval(tds[k0 + 7]))
    dasList.append(actv)

  # showList(self, 'Daily Adds & Subs', dasList, acIdx)
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