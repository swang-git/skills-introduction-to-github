#!/usr/bin/python
import sys, os, re, pytz
# from glob import glob
from datetime import datetime
from dateutil.parser import parse

from sqlalchemy import create_engine, func, desc, text
from sqlalchemy.orm import sessionmaker
from Models import BankStatementAsset, BankStatementNote, BankAccountActivity
from dataclasses import dataclass, field
import argparse

dbconf = "mysql://swang:VVKKll11##@localhost/devx?charset=utf8mb4"
# engine = create_engine(dbconf, encoding='utf8', echo=False)
engine = create_engine(dbconf, echo=False)

Session = sessionmaker(bind=engine)
session = Session()

parser = argparse.ArgumentParser(description='build load bank monthly statement application')
parser.add_argument('yyyymm', nargs='?', default='202403', metavar='yyyymm', type=str, help='load bank monthly statement application')
args = parser.parse_args()
# print(args)
yyyymm = args.yyyymm
print('==== load chase [%s] monthly statement ====='%yyyymm)

@dataclass
class StmtData:
    start_date: datetime = field(default=None)
    end_date: datetime = field(default=None)
    chk_start_bal: int = field(default=0.0)
    chk_end_bal: str = field(default=None)
    sav_start_bal: int = field(default=0.0)
    sav_end_bal: str = field(default=None)
    tran_date: str = field(default=None)
    sav_depo: str = field(default=None)
    tran_cnt: int = field(default=0)
    annual_yield: str = field(default=None)
    interest_paid_this_month: str = field(default=None)
    interest_paid_year_to_date: float = field(default=None)
    start_bal: float = field(default=0.0)
    end_bal: float = field(default=None)

def getStatementData(yyyymm):
    f = open("/sites/webdata/docs/Chase/" + yyyymm + ".txt", "r")
    dax = StmtData(None, None)
    for line in f:
        if dax.start_date is None and re.search(r"(.*)\d{4}\s+through\s+(.*)\d{4}$", line):
            # print(line)
            x = line.split('through')
            dax.start_date = parse(x[0].strip()).strftime('%Y-%m-%d')
            dax.end_date = parse(x[1].strip()).strftime('%Y-%m-%d')
            dax.year = dax.end_date.split('-')[0]
            dax.month = dax.end_date.split('-')[1]
            # print("start_date[%s] end_date[%s]"%(dax.start_date, dax.end_date))
        elif re.search("^Chase Total Checking", line):
            x = line.split(' ')
            dax.chk_acct = x[3].strip()
            dax.chk_start_bal = float(x[4].strip().replace('$', '').replace(',', ''))
            dax.chk_end_bal = float(x[5].strip().replace('$', '').replace(',', ''))
            # print("chk_acct[%s] chk_start_bal[%s] chk_end_bal[%s]"%(dax.chk_acct, dax.chk_start_bal, dax.chk_end_bal))
        elif re.search("^Chase Savings", line):
            x = line.split(' ')
            dax.sav_acct = x[2].strip()
            dax.sav_start_bal = float(x[3].strip().replace('$', '').replace(',', ''))
            dax.sav_end_bal = float(x[4].strip().replace('$', '').replace(',', ''))
            # print("sav_acct[%s] sav_start_bal[%s] sav_end_bal[%s]"%(dax.sav_acct, dax.sav_start_bal, dax.sav_end_bal))
        elif re.search(r"^\d\d\/\d\d", line):
            dax.tran_date = dax.year + '-' + line.split(' ')[0].replace('/', '-')
            dax.tran_cnt += 1
        elif re.search("^Deposits and Addition", line):
            x = line.split(' ')
            dax.sav_depo = float(x[3].strip())
            # print("sav_depo[%s]"%(dax.sav_depo))
        elif re.search("^Annual Percentage Yield Earned This Period", line):
            x = line.split(' ')
            dax.annual_yield= float(x[len(x)-1].strip().replace('%', ''))
        elif re.search("^Interest Paid This Period", line):
            x = line.split(' ')
            dax.interest_paid_this_month= float(x[len(x)-1].strip().replace('$', ''))
        elif re.search("^Interest Paid Year-to-Date", line):
            x = line.split(' ')
            dax.interest_paid_year_to_date= float(x[len(x)-1].strip().replace('$', ''))

    f.close()
    dax.start_bal = dax.chk_start_bal + dax.sav_start_bal
    dax.end_bal = dax.chk_end_bal + dax.sav_end_bal
    return dax

def addAsset(da):
    dat = BankStatementAsset()
    dat.user_id = 1
    dat.bank = 'Chase'
    dat.year = da.end_date.split('-')[0]    
    dat.month = da.end_date.split('-')[1]
    dat.begin_date = da.start_date
    dat.end_date = da.end_date
    dat.primary_account = da.chk_acct 
    dat.tran_cnt = da.tran_cnt
    dat.begin_balance = da.start_bal 
    dat.end_balance = da.end_bal
    print(dat)

    rec = session.query(BankStatementAsset).filter_by(
        bank=dat.bank, year=dat.year, month=dat.month)
    if rec.scalar() is None:
        print('adding:assts', dat.bank, dat.year, dat.month)
        session.add(dat)
        session.commit()
    else:
        print('========= Asset exists:', dat.bank, dat.year, dat.month)

def addNotes(da, i, notes, amount):
    dat = BankStatementNote()
    dat.user_id = 1
    dat.bank = 'Chase'
    dat.year = da.end_date.split('-')[0]    
    dat.month = da.end_date.split('-')[1]
    dat.note_id = i 
    dat.notes = notes
    dat.amount = amount
    print(dat)

    rec = session.query(BankStatementNote).filter_by(
        bank=dat.bank, year=dat.year, month=dat.month, note_id=i)
    if rec.scalar() is None:
        print('adding notes', dat.bank, dat.year, dat.month, i, notes, amount)
        session.add(dat)
        session.commit()
    else:
        print('========= Note exists:', dat.bank, dat.year, dat.month, i, notes, amount)

def addActivities(da, i, acct_type):
    if acct_type == 'Savings':
        desc = 'Interest'
        bbal = da.sav_start_bal
        amnt = da.sav_depo
        ebal = da.sav_start_bal + amnt
        tran_date = da.tran_date
        acct_num = da.sav_acct
    else:
        desc = 'No Activities'
        bbal = da.chk_start_bal
        amnt = 0
        ebal = da.chk_start_bal + amnt
        tran_date = None
        acct_num = da.chk_acct

    dat = BankAccountActivity()
    dat.user_id = 1
    dat.bank = 'Chase'
    dat.year = da.end_date.split('-')[0]    
    dat.month = da.end_date.split('-')[1]
    dat.acct_type = acct_type 
    dat.account_num = acct_num
    dat.tran_num = i 
    dat.tran_date = tran_date
    dat.description = desc
    dat.begin_balance = bbal
    dat.amount = amnt
    dat.end_balance = ebal
    print(dat)

    rec = session.query(BankAccountActivity).filter_by(
        bank=dat.bank, year=dat.year, month=dat.month, tran_num=i)
    if rec.scalar() is None:
        print('adding activities', dat.bank, dat.year, dat.month, i, desc, amnt)
        session.add(dat)
        session.commit()
    else:
        print('========= Activity exists:', dat.bank, dat.year, dat.month, i, desc, amnt)

sda = getStatementData(yyyymm)
addAsset(sda)

addNotes(sda, 1, 'Annual Percentage Yield Earned This Period', sda.annual_yield)
addNotes(sda, 2, 'Interest Paid This Period', sda.interest_paid_this_month)
addNotes(sda, 3, 'Interest Paid Year To Date', sda.interest_paid_year_to_date)

addActivities(sda, 1, 'Savings')
addActivities(sda, 2, 'Checking')
print(sda)

sys.exit(0)

# from pathlib import Path
# import calendar
# import sqlalchemy
# from sqlalchemy import create_engine, func, desc
# from sqlalchemy.orm import sessionmaker
# from Models import recorded, channel_ALL, channel

def getLastMythBackendLogFile():
    # print('-fn-getLastMythBackendLogFile')
    pth = '/var/log/mythtv/mythbackend'
    sfl = sorted(glob(pth+"*.log"), key=os.path.getmtime)
    # sfl = sorted(glob(pth+"*.log"), key=os.path.getsize)
    # for f in sfl: print(f)
    return sfl[-1]

def getCommflagLines(filename):
    lines = []
    for line in open(filename, 'r'):
        if re.search('Commflag_', line):
            lines.append(line)
    return lines

print('========= MythCommflagCheck ==========')
mlogfile = getLastMythBackendLogFile()
print("-CK- the log file is:[%s]"%mlogfile)
lines = getCommflagLines(mlogfile)

utctz = pytz.timezone('UTC')
tz = pytz.timezone('America/New_York')
dtfmt = '%Y-%m-%d %H:%M:%S' 
cmm = []
for line in lines:
    if line == '' or re.search(' E ', line) or re.search('Broken filename', line): continue
    # print(line)
    x = line.split('Starting for ')
    xx = x[0].split(' ')
    xa = x[1].split(' recorded ')
    nm = xa[0].replace('"', '')
    # print(xa)
    xc = xa[1]
    ch = xc.split(' ')[2]
    xt = xc.split(' ')[4]
    sx = re.sub('[T|Z]', ' ', xt.strip()).strip()
    # print('====== utc time[%s]'%sx)
    utcdt = datetime.strptime(sx, dtfmt)
    utctm = utcdt.replace(tzinfo=utctz)
    utctx = utctm.astimezone(tz)
    st = utctx.strftime(dtfmt)
    dt = xx[0] + ' ' + xx[1].split('.')[0]
    cmm.append([dt, ch + ' ' + st + ' ' + nm])

for i, x in enumerate(cmm):
    d1 = x[0]
    nm = re.sub(r':00\s+', ' ', x[1])
    d2 = datetime.now(tz).strftime(dtfmt)
    try: d2 = cmm[i+1][0]
    except IndexError: pass
    df = datetime.strptime(d2, dtfmt) - datetime.strptime(d1, dtfmt)
    n = 1
    if i + 1 >= 10: n = 0
    idx = f'{" "*n}' + str(i+1)
    print("%s: %s [%s] %s"%(idx, d1, df, nm))

sys.exit(0)


limitNum = 20
if len(sys.argv) > 1: limitNum = int(sys.argv[1])
print('==== starting link-myth-title.py limitNum:%s -- using sqlchemy version:%s'%(limitNum,sqlalchemy.__version__))

dbconf="mysql://mythtv:mythtvVVKK0#@localhost/mythconverg?charset=utf8mb4"
engine = create_engine(dbconf, encoding='utf8', echo=False)
Session = sessionmaker(bind=engine)
session = Session()

from datetime import datetime
import time

def minuteDiff(starttime, endtime): # duration in minutes
    fmt = '%Y-%m-%d %H:%M:%S'
    d1 = datetime.strptime(str(starttime), fmt)
    d2 = datetime.strptime(str(endtime), fmt)

    # Convert to Unix timestamp
    d1_ts = time.mktime(d1.timetuple())
    d2_ts = time.mktime(d2.timetuple())
    return int((d2_ts-d1_ts) / 60)

def getFullpath(basename):
    if   os.path.exists('/home/swang/atv/' + basename): return '/home/swang/atv/' + basename
    elif os.path.exists('/home/swang/btv/' + basename): return '/home/swang/btv/' + basename
    elif os.path.exists('/home/swang/ctv/' + basename): return '/home/swang/ctv/' + basename
    elif os.path.exists('/home/swang/dtv/' + basename): return '/home/swang/dtv/' + basename

def getFullDirname(basename):
    if   os.path.exists('/home/swang/atv/' + basename): return '/home/swang/atv/'
    elif os.path.exists('/home/swang/btv/' + basename): return '/home/swang/btv/'
    elif os.path.exists('/home/swang/ctv/' + basename): return '/home/swang/ctv/'
    elif os.path.exists('/home/swang/dtv/' + basename): return '/home/swang/dtv/'
    
def cleanupRecording():
    for idx, rec in enumerate(session.query(recorded).filter(recorded.watched == 2).order_by(recorded.starttime.desc())):
        recordedfile = getFullpath(rec.basename)
        if recordedfile == None:
            print('No file found in file system for [%s] [%s] [%s] delete record in recorded table'%(rec.basename, rec.starttime, rec.title))
            session.query(recorded).filter(recorded.basename == rec.basename).delete(synchronize_session=False)
            continue
        basename = rec.basename
        dirname = getFullDirname(basename)
        for path in Path(dirname).rglob(basename+'*'):
            print("removing [%s]"%(dirname + path.name))
            try:
                os.remove(dirname + path.name)
            except OSError as errmsg:
                print(errmsg)

        if len(rec.title) > 0 and recordedfile != None: print("Removing [%s] [%s]"%(recordedfile, rec.title))
        session.query(recorded).filter(recorded.basename == rec.basename).delete(synchronize_session=False)

########### main ###########
cleanupRecording()

misfiles = []
videodir = '/home/swang/vlnk/'
# videodir = '/sites/tmp/vlnk/'
# files = os.walk(videodir)
files = os.listdir(videodir)
from datetime import date
today = str(date.today()) + '%'
for file in files:
    file = videodir + file
    # print('=== remove symbolic link [%s]'%file)
    os.unlink(file)

# for idx, rec in enumerate(session.query(recorded).order_by(recorded.starttime)):
# for idx, rec in enumerate(session.query(recorded).filter(recorded.starttime.like(today)).order_by(recorded.starttime)):
# for idx, rec in enumerate(session.query(recorded).filter(recorded.watched == 0, recorded.chanid < 16804).order_by(recorded.starttime.desc()).limit(limitNum)):
for idx, rec in enumerate(session.query(recorded).filter(recorded.watched == 0).order_by(recorded.starttime.desc()).limit(limitNum)):
    # stm = datetime.strftime(rec.starttime + timedelta(hours=-5), '%m-%d %H:%M') # winter time
    delta = calendar.timegm(datetime.now(tz=UTC).timetuple()) - calendar.timegm(datetime.now().timetuple())
    stm = datetime.strftime(rec.starttime + timedelta(hours=-round(delta/60/60)), '%m-%d %H:%M')  # work with daylight time saving
    durationMinutes = str(minuteDiff(rec.starttime, rec.endtime))

    recordedfile = getFullpath(rec.basename)
    if recordedfile == None:
        if idx + 1 < limitNum: print('idx[%d]delete record row for no recorded file with basename = [%s]'%(idx, rec.basename)) 
        session.query(recorded).filter(recorded.basename == rec.basename).delete(synchronize_session=False)
        continue

    channux = session.query(channel_ALL).filter_by(chanid = rec.chanid).first()
    if channux == None: 
        channux = session.query(channel).filter_by(chanid = rec.chanid).first()
        if channux == None: 
            print('No such chanid[%s] [%s] in channel_ALL' %(rec.chanid, rec.title))
            continue
    channum = channux.channum.replace('_', '.')
    # chan = session.query(channel).filter_by(chanid = rec.chanid).first()
    # if chan == None: chan = session.query(channel_old_id).filter_by(chanid = rec.chanid).first()
    # channum = chan.channum
    nsp = 1
    if len(durationMinutes) == 2: durationMinutes = f'{" "*nsp}' + durationMinutes
    if len(channum) == 3: channum = f'{" "*nsp}' + channum
    fsz = format(rec.filesize / 1024 / 1024 / 1024, '.2f') + 'G'
    if len(fsz) == 5: fsz = f'{" "*nsp}' + fsz
    tit = re.sub('/', '-', rec.title.strip())
    # chanid = str(rec.chanid)[1:3] + '-' + str(rec.chanid)[4:]
    chanid = str(rec.chanid)
    head = str(idx+1)
    if idx+1 < 10: head = '00' + str(idx+1)
    elif idx+1 < 100: head = '0' + str(idx+1)
    dst = channum + ' ' + stm + ' ' + fsz + ' ' + durationMinutes + 'm 《' + tit + '》'
    # dst = head + ' ' + channum + ' ' + stm + ' ' + fsz + ' 《' + tit + '》'
    # dst = head + ' ' + channum + ' ' + stm + ' [' + tit + '] ' + fsz
    # dst = head + ' ' + chanid + ' ' + channum + ' ' + stm + ' [' + tit + '] ' + fsz
    # dst = re.sub('[(|)]', '', dst).replace(' ', '_').replace('"', '').replace('&', 'n').replace('\'', '’')
    dst = re.sub('[(|)]', '', dst).replace('"', '').replace('&', 'n').replace('\'', '’')
    dst += '.ts'
    # print(idx+1, recordedfile, ' ==> ', dst)
    os.chdir(videodir)
    print(head, recordedfile, ' ==> ', dst)
    os.symlink(recordedfile, dst)
for misf in misfiles: print(misf)
# print('==== ended link-myth-title.py')

# cleanupRecording()
sys.exit(0)
