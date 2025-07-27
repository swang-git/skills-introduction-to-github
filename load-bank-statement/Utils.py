''' utilities '''
import re
import argparse
# from datetime import datetime
from datetime import timedelta, datetime
from calendar import calendar
DEBUG = False
DEBUG = True

def showList(self, tag, list, idx):
  if not DEBUG or len(list) == 0: return
  acNam = self.accountAssets[idx].acctName
  acNum = self.accountAssets[idx].acctNum
  tag += ' for account %s ~ %s'%(acNam, acNum)
  print('\n\t\t\t', tag)
  for i, x in enumerate(list):
    p = x[:]
    p[7] = padsp(p[7], 5)
    p[8] = p[8][0:28]
    p[8] = padsp(p[8], 28)
    print(p)

def addDays(ymd, days):
  """ add num of days to the given date yyyy-mm-dd """
  d = datetime.strptime(ymd, '%Y-%m-%d')
  return (d + timedelta(days=days)).strftime('%Y-%m-%d')

def dg(ins):
  ''' debugging print out '''
  if DEBUG:
    print(ins)

def mval(ins):
  """ clean up to float """
  ins = ins.strip()
  return float(re.sub('[$|,|%]', '', ins))

def get_last_day_of_month(year, month):
  _, num_days = calendar(year, month)
  last_day = datetime.date(year, month, num_days)
  return last_day

def padsp(s, width):
    # if s == None: return 'Empty'
    # else: return '{msg:>{width}}'.format(msg=s,width=width)
    if s == None: s = ''
    return '{msg:>{width}}'.format(msg=s,width=width)
def my_argparse():
    parser = argparse.ArgumentParser(description='process bank monthly statements for BOA, chase and fidelity')
    parser.add_argument('--yermon', '-m', metavar='str', type=str, default='202102', help='payment due day e.g.202102, default to 202102')
    parser.add_argument('--ccbank', '-b', metavar='str', type=str, default='BOA',    help='Fidelity/Chase/BOA, default to BOA')
    parser.add_argument('--actype', '-t', metavar='str', type=str, default="savings",
      help='account type:\r\n\
         for BOA:savings/checking or both, default to savings\n\
         for Chase: None for all -- there is only statement to process\n\
         for Fidelity: ira/roth/ann or none for all')
    return parser.parse_args()

def pedsp(s, width): return '{msg:<{width}}'.format(msg=s, width=width)
def isFloat(str):
  try:
    float(str)
    return True
  except ValueError:
    # print("Not a float")
    return False

def cleanMoney(line):
  avar = re.sub('[$|,|%]', '', line).strip('.')
  if avar == '-0.00': avar = '0.00'
  return avar

def showHolding(o):
  print(o)
  date = '00-00-00' if o.date == None else o.date
  amnt = o.amnt if o.amnt != None else '0.00'
  print('%s %s %s %s %s %s %s' % (padsp(o.desc[0:52], 53), 'Activ ' + str(o.tran), date, o.aNum, padsp(o.balB, 8), padsp(amnt, 8), padsp(o.balE, 8)))

def showActivity(o):
  date = '00-00-00' if o.date == None else o.date
  amnt = o.amnt if o.amnt != None else '0.00'
  print('%s %s %s %s %s %s %s' % (padsp(o.desc[0:52], 53), 'Activ ' + str(o.tran), date, o.aNum, padsp(o.balB, 8), padsp(amnt, 8), padsp(o.balE, 8)))

def showNotes(i, o):
  amnt = o.amnt if o.amnt != None else '-'
  print('%s %s %s %s %s %s' % (padsp(o.notes, 53), 'Notes ' + str(i), o.bank, o.year, o.month, padsp(amnt, 4)))

def showConts(tag, obj):
  # print(vars(obj))
  print('    -0^0- %s -o^o-'%tag)
  for attr in dir(obj):
    # Will print parentheses immediately after any callables
    # print(attr + '()') if callable(getattr(obj, attr)) else print(attr)
    if not callable(getattr(obj, attr)) and '__' not in attr and 'txt' != attr:
       print('\t%s:[%s]' % (attr, getattr(obj, attr)))

def showDict(tag, dict):
  if tag != None: print(' --- %s ---' % tag)
  if dict == None: return
  for key, val in dict.items():
    print('%s: %s' % (padsp(key, 10), val))

def showDictO(tag, dict):
  if tag: print('\t\t\t\t\t -O- %s -O-'%tag)
  for key, val in dict.items():
    # key = padsp()
    print(padsp('['+key+']', 10) + ' : [%s]' % (val))
    # print('\t\t', key, val)

def xmap(self, tag, acIdx):
  txt = tag.text.strip()
  txt = re.sub('\s+', ' ', txt)
  txt = txt.replace(self.SPRXX, 'SPRXX')
  txt = re.sub(self.SPAXX, 'SPAXX', txt)
  txt = re.sub(self.FDRXX, 'FDRXX', txt)
  txt = re.sub(self.FSKAX, 'FSKAX', txt)
  txt = re.sub(self.FXAIX, 'FXAIX', txt)
  # txt = re.sub(self.FZDXXM, 'FZDXX', txt)
  # txt = re.sub(self.FZDXXP, 'FZDXX', txt)
  txt = re.sub(self.FZDXX, 'FZDXX', txt)
  txt = re.sub(self.FNJXX, 'FNJXX', txt)
  txt = re.sub(self.SPA_FDR, 'SPAXX' if acIdx == 0 else 'FDRXX', txt)
  txt = re.sub(self.MSI, 'MSI', txt)
  txt = re.sub(self.CSCO, 'CSCO', txt)
  txt = re.sub(self.MSFT, 'MSFT', txt)
  txt = re.sub(self.T, 'T', txt)
  txt = re.sub(self.QBNYQ, 'QBNYQ', txt)
  txt = re.sub(self.QPCBQ, 'QPCBQ', txt)
  txt = re.sub(self.FTXWH, 'FTXWH', txt)
  txt = re.sub(self.STXWH, 'STXWH', txt)
  txt = re.sub(self.FFTWX, 'FFTWX', txt)
  return txt

def gtxt(tag):
  txt = tag.text.strip()
  txt = re.sub('\s+', ' ', txt)
  return txt

def gsym(self, tag):
  # ret = tag.text.strip().replace('FIDELITY GOVERNMENT MONEY MARKET', 'SPAXX').replace('  ', '')
  # ret = ret.replace('FIDELITY GOVERNMENT CASH', 'FDRXX').replace('  ', '')
  txt = tag.text.strip()
  txt = re.sub('\s+', ' ', txt)
  txt = re.sub(self.QPCBQ, 'QPCBQ', txt)
  txt = re.sub(self.QBNYQ, 'QBNYQ', txt)
  txt = txt.replace(self.SPAXX, 'SPAXX').replace('\s+', '')
  txt = txt.replace(self.FDRXX, 'FDRXX').replace('\s+', '')
  return txt
def gval(tag):
  if tag.text.strip() == '': return None
  elif tag.text.strip() == 'N/A': return None
  else: return mval(tag.text.strip())
  # else: return padsp(mval(tag.text.strip()), 10)

def gpad(tag): return padsp(mval(tag.text.strip()), 8)
def gdat(year, tag): return year + '-' + tag.text.strip().replace('/', '-')

def getListFromTds(tdsoup):
  dictList = []
  tds = tdsoup.findAll('td')
  for i, td in enumerate(tds):
    k0 = i * 2
    k1 = k0 + 1
    try: dictList.append({gtxt(tds[k0]): gtxt(tds[k1])})
    except: pass
  return dictList

def showTag(tag):
  print(padsp('-o^o- ' + tag + ' -o^o-', 60))
  # print(padsp('-o^o- Account Details -o^o-', 80))

def getDictValByPartialKey(dictList, pkey):
  for dict in dictList:
    for key, val in dict.items():
      if pkey in key: return val
  return None


