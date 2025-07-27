import fitz
import sys, re
from datetime import datetime
from Utils import padsp, isFloat

from pdfminer.converter import PDFPageAggregator
from pdfminer.layout import LAParams, LTFigure, LTTextBox
from pdfminer.pdfdocument import PDFDocument
from pdfminer.pdfinterp import PDFPageInterpreter, PDFResourceManager
from pdfminer.pdfpage import PDFPage, PDFTextExtractionNotAllowed
from pdfminer.pdfparser import PDFParser
from pathlib import Path
# import os.path

class Statement:
  def __getattr__(self, key): return None
  def __init__(self, bank, yearMonth, filename):
    self.bank = bank
    self.ymon = yearMonth
    self.year = yearMonth[0:4]
    # self.prevyear = str(int(self.year) - 1)
    self.month = yearMonth[4:6]
    self.filename = filename
    self.txt = ''
    self.rootdir = '/sites/webdata/docs/' + self.bank + '/'
    self.totalAssets
    self.statementNotes = []
    self.accountActivity = []
    self.tran = 1
    if filename: self.setFilepath()

    self.FXAIX = 'FIDELITY 500 INDEX FUND|FIDELITY 500 INDEX'
    self.FSKAX = 'FIDELITY TOTAL MARKET INDEX FUND|FIDELITY TOTAL'
    self.SPRXX = 'FIDELITY MONEY MARKET'
    # self.SPAXX = 'FIDELITY GOVERNMENT MONEY MARKET|FIDELITY GOVERNMENT'
    self.FZDXX = 'FIDELITY MONEY MARKET|FIDELITY MMKT PREMIUM CLASS|FIDELITY MMKT|FIDELITY MONEY'
    self.SPAXX = 'FIDELITY GOVERNMENT MONEY MARKET' # in account Individual 0
    self.FDRXX = 'FIDELITY GOVERNMENT CASH'  # in account IRA 1
    self.SPA_FDR = 'FIDELITY GOVERNMENT'  # SPAXX or FDRXX -- check the account activity to identify
    self.FNJXX = 'FIDELITY NJ MUNICIPAL MONEY MKT|FIDELITY NJ'
    self.MSI = 'MOTOROLA SOLUTIONS INC COM NEW|MOTOROLA SOLUTIONS'
    self.CSCO = 'CISCO SYS INC COM|CISCO SYSTEMS INC'
    self.MSFT = 'MICROSOFT CORP'
    self.T = 'AT&T INC COM USD1'
    self.QBNYQ = 'FDIC INSURED DEPOSIT AT BNY MELLON NOT COVERED BY SIPC'
    self.QPCBQ = 'FDIC INSURED DEPOSIT AT CITIBANK IRA NOT COVERED BY SIPC'
    self.FFTWX = 'FIDELITY FREEDOM 2025|FIDELITY'
    self.FTXWH = 'FEDERAL TAX WITHHELD'
    self.STXWH = 'NJ STAT WTH'


  def setFilepath(self):
    self.filepath = self.rootdir + str(self.ymon) + self.filename
    if self.filename == '_annuity.pdf':
      m = int(self.month)
      qnum = 'Q1'
      if 0 < m <= 2: qnum = 'Q4'; pyear = str(int(self.year) - 1)
      elif m == 3: qnum = 'Q1'; pyear = self.year
      elif 3 < m <= 5: qnum = 'Q1'; pyear = self.year
      elif m == 6: qnum = 'Q2'; pyear = self.year
      elif 6 < m <= 8: qnum = 'Q2'; pyear = self.year
      elif m == 9: qnum = 'Q3'; pyear = self.year
      elif 9 < m <= 11: qnum = 'Q3'; pyear = self.year
      elif m == 12: qnum = 'Q4'; pyear = self.year
      self.filepath = self.rootdir + pyear + qnum + self.filename

    myFile = Path(self.filepath)
    if not myFile.is_file():
      print('File:[%s] does not exist, exit...' % self.filepath)
      sys.exit(101)
    # print('self.filepath:[%s]' % self.filepath)

  def setTxtUsingFitz(self) -> str:
    self.setFilepath()
    self.txt = ''
    with fitz.open(self.filepath) as doc:
      # for page in doc: self.txt += page.getText("text").strip()
      for page in doc: self.txt += page.getText()
    self.cleanTxt()

  def setTxtUsingPdfminer(self):
    if self.filepath == None: self.setFilepath()
    text = ""
    stack = []
    with open(self.filepath, 'rb') as f:
      parser = PDFParser(f)
      doc = PDFDocument(parser)
      for page in list(PDFPage.create_pages(doc)):
        rsrcmgr = PDFResourceManager()
        device = PDFPageAggregator(rsrcmgr, laparams=LAParams())
        interpreter = PDFPageInterpreter(rsrcmgr, device)
        interpreter.process_page(page)
        layout = device.get_result()

        for obj in layout:
          if isinstance(obj, LTTextBox):
              text += obj.get_text()

          elif isinstance(obj, LTFigure):
              stack += list(obj)
    self.txt = text
    # print('XXXX text', text)
    self.cleanTxt()

  def cleanTxt(self):
    self.lines = []
    for line in self.txt.splitlines():
      linx = re.sub('\s+', ' ', line.strip())
      if re.search('^AMOUNT$', linx) or linx == '' or re.search('^\*', linx) \
        or re.search('^•', linx) or len(linx) > 66 or len(linx) < 3: continue
      self.lines.append(linx)

  def setTxt(self):
      if self.bank == 'Fidelity': pass
      elif self.bank == 'BOA': self.setTxtUsingFitz()
      elif self.bank == 'Chase': self.setTxtUsingPdfminer()
      self.cleanTxt()
      # for line in self.lines: print(line)

  def showTxt(self):
      if self.bank == 'Fidelity': self.setTxtUsingFitz()
      elif self.bank == 'BOA': self.setTxtUsingFitz()
      elif self.bank == 'Chase': self.setTxtUsingPdfminer()
      self.cleanTxt()
      for line in self.lines: print(line)

  def cleanMoney(self, line):
    return re.sub('[$|,|%]', '', line)

  def showDates(self):
    print('beginning_date[%s], ending_date[%s]'%(self.sdate, self.edate))

  def showData(self):
    a = self.accountSummary
    print('%s, %s, %s, %s, %s, %s, %s'%(a.date, 'B O A', a.type, padsp(a.deposits, 8), padsp(a.withdrawals, 8), a.checks, a.fees))
    for ac in self.accounts:
      p = ac['accountData']
      print('%s, %s, %s, %s, %s, %s, %s, %s'%(p.start_date, p.end_date, 'B O A', p.accountNum, padsp(p.bBalance, 8), padsp(p.eBalance, 8), p.account, p.accountNum))
      av = ac['acctActivity']
      # print('av type', type(av))
      if av != None:
        for a in av:
          print('  %s, %s, %s, %s, %s'%(a.date, 'B O A', a.accountNum, padsp(a.amount, 7), a.description))

class AssetsBase:
  def __getattr__(self, key): return None
  def __init__(self, bank, year, month, sbal, ebal, tran=0):
    self.bank = bank
    self.year = year
    self.mnth = month
    self.balB = sbal
    self.balE = ebal
    self.tran = tran
  def setTran(self, tran):
    self.tran += tran

class Assets(AssetsBase):
  def __getattr__(self, key): return None
  def __init__(self, bank, year, month, sbal, ebal, tran, acctNum, acctType):
    super().__init__(bank, year, month, sbal, ebal, tran)
    self.acctNum = acctNum
    self.acctType = acctType

class TotalAssets(AssetsBase):
  def __getattr__(self, key): return None
  def __init__(self, bank, year, month, sbal, ebal, tran, dateB, dateE, primaryAcct):
    super().__init__(bank, year, month, sbal, ebal, tran)
    self.dateB = dateB
    self.dateE = dateE
    self.primaryAcct = primaryAcct

class StatementNotes():
  def __getattr__(self, key): return None
  def __init__(self, bank, year, mnth, noteId, note, amnt):
    self.bank = bank
    self.year = year
    self.month = mnth
    self.noteId = noteId
    self.notes = note
    self.amnt = amnt

class AccountActivity(AssetsBase):
  def __getattr__(self, key): return None
  def __init__(self, bank, year, month, sbal, ebal, tran, aNum, type, date, desc, amnt):
    super().__init__(bank, year, month, sbal, ebal, tran)
    self.aNum = aNum
    self.type = type
    self.date = date
    self.desc = desc
    self.amnt = amnt
  def show(self):
    # print(self.date, self.bank, self.accountNum, self.amount.description)
    print(
      '\tdate:[%s]\n\tbank:[%s]\n\tacct:[%s]\n\tamnt:[%s]\n\tdesc:[%s]'
        %(
        self.date,
        self.bank,
        self.accountNum,
        self.amount,
        self.description
      ))
class bym:
  def __getattr__(self, key): return None
  def __init__(self, bank, year, mnth):
    self.bank = bank
    self.year = year
    self.mnth = mnth

class Holding:
  def __getattr__(self, key): return None
  def __init__(self, *args):
    # print('    XXX', args[3][0], type(args[3]))
    vec = args[1]
    if type(vec) == list:
      self.bank = args[0].bank
      self.year = args[0].year
      self.mnth = args[0].mnth
      self.anam = vec[0]
      self.anum = vec[1]
      self.quan = vec[2]
      self.pric = vec[3]
      self.balB = vec[4]
      self.balE = vec[5]
      self.cost = vec[6]
      self.symb = vec[7]
  def show(self):
    print('%s, %s, %s, %s, %s, quan:%s, price:%s, balB:%s, balE:%s, cost[%s]'
    %(self.bank,self.year,self.mnth,self.anum,padsp(self.symb, 5),padsp(self.quan,9),padsp(self.pric,6),padsp(self.balB, 9),padsp(self.balE,9),padsp(self.cost,9)))
